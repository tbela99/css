<?php

namespace TBela\CSS;

use Exception;
use stdClass;
use TBela\CSS\Interfaces\ElementInterface;
use TBela\CSS\Interfaces\ParsableInterface;
use TBela\CSS\Interfaces\RuleListInterface;
use TBela\CSS\Parser\Helper;
use TBela\CSS\Parser\ParserTrait;
use TBela\CSS\Parser\SyntaxError;
use function preg_replace_callback;
use function substr;

/**
 * Css Parser
 * @package TBela\CSS
 * ok */
class Parser implements ParsableInterface
{

    use ParserTrait;

    protected int $parentOffset = 0;
    protected ?stdClass $parentStylesheet = null;
    protected ?stdClass $parentMediaRule = null;

    protected array $errors = [];

    protected ?stdClass $ast = null;
    protected ?RuleListInterface $element = null;

    /**
     * css data
     * @var string
     * @ignore
     */
    protected string $css = '';

    /**
     * @var string
     * @ignore
     */
    protected string $src = '';
    /**
     * @var array
     * @ignore
     */
    protected array $options = [
        'capture_errors' => true,
        'flatten_import' => false,
        'allow_duplicate_rules' => ['font-face'], // set to true for speed
        'allow_duplicate_declarations' => false
    ];

    /**
     * Parser constructor.
     * @param string $css
     * @param array $options
     */
    public function __construct($css = '', array $options = [])
    {
        if ($css !== '') {

            $this->setContent($css);
        }

        $this->setOptions($options);
    }

    /**
     * load css content from a file
     * @param string $file
     * @param string $media
     * @return Parser
     * @throws Exception
     */

    public function load($file, $media = '')
    {

        $this->src = Helper::absolutePath($file, Helper::getCurrentDirectory());
        $this->css = $this->getFileContent($file, $media);
        $this->ast = null;
        $this->element = null;
        return $this;
    }

    /**
     * parse css file and append to the existing AST
     * @param string $file
     * @param string $media
     * @return Parser
     * @throws SyntaxError
     * @throws Exception
     */
    public function append($file, $media = '')
    {

        return $this->merge((new self('', $this->options))->load($file, $media));
    }

    /**
     * @param Parser $parser
     * @return Parser
     * @throws SyntaxError
     */
    public function merge($parser)
    {

        assert($parser instanceof self);

        if (is_null($this->ast)) {

            $this->doParse();
        }

        if (is_null($parser->ast)) {

            $parser->doParse();
        }

        array_splice($this->ast->children, count($this->ast->children), 0, $parser->ast->children);
        return $this;
    }

    /**
     * parse css and append to the existing AST
     * @param string $css
     * @param string $media
     * @return Parser
     * @throws SyntaxError
     */
    public function appendContent($css, $media = '')
    {
        if ($media !== '' && $media != 'all') {

            $css = '@media ' . $media . ' { ' . rtrim($css) . ' }';
        }

        $this->css .= rtrim($css);

        if (is_null($this->ast)) {

            $this->doParse();
        }

        $this->analyse();

        return $this;
    }

    /**
     * set css content
     * @param string $css
     * @param string $media
     * @return Parser
     */
    public function setContent(string $css, string $media = '')
    {

        if ($media !== '' && $media != 'all') {

            $css = '@media ' . $media . '{ ' . rtrim($css) . ' }';
        }

        $this->css = $css;
        $this->src = '';
        $this->ast = null;
        $this->element = null;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {

        return $this->css;
    }

    /**
     * set the parser options
     * @param array $options
     * @return Parser
     */
    public function setOptions(array $options)
    {

        foreach ($this->options as $key => $v) {

            if (isset($options[$key])) {

                $this->options[$key] = $options[$key];

                if ($key == 'allow_duplicate_declarations') {

                    if (is_string($this->options[$key])) {

                        $this->options[$key] = [$this->options[$key]];
                    } else if (is_array($this->options[$key])) {

                        $this->options[$key] = array_flip($this->options[$key]);
                    }
                }
            }
        }

        $this->ast = null;
        return $this;
    }

    /**
     * parse Css
     * @return RuleListInterface|null
     * @throws SyntaxError
     */
    public function parse()
    {

        if (is_null($this->ast)) {

            $this->doParse();
        }

        return Element::getInstance($this->ast);
    }

    /**
     * @inheritDoc
     * @throws SyntaxError
     */
    public function getAst()
    {

        if (is_null($this->ast)) {

            $this->doParse();
        }

        return clone $this->ast;
    }

    /**
     * @param ElementInterface $element
     * @return Parser
     */
    public function setAst(ElementInterface $element)
    {

        $this->ast = $element->getAst();
        return $this;
    }

    public function deduplicate($ast)
    {

        if ($this->options['allow_duplicate_rules'] !== true ||
            $this->options['allow_duplicate_declarations'] !== true) {

            switch ($ast->type) {

                case 'Stylesheet':

                    return $this->deduplicateRules($ast);

                case 'AtRule':

                    return !empty($ast->hasDeclarations) ? $this->deduplicateDeclarations($ast) : $this->deduplicateRules($ast);

                case 'Rule':

                    return $this->deduplicateDeclarations($ast);
            }
        }

        return $ast;
    }

    /**
     * compute signature
     * @param stdClass $ast
     * @return string
     * @ignore
     */
    protected function computeSignature($ast)
    {

        $signature = 'type:' . $ast->type;

        $name = $ast->name ?? null;

        if (isset($name)) {

            $signature .= ':name:' . $name;
        }

        $value = $ast->value ?? null;

        if (isset($value)) {

            $signature .= ':value:' . (is_string($value) ? Value::parse($value, $name) : $value)->render(['convert_color' => 'hex', 'compress' => true]);
        }

        $selector = $ast->selector ?? null;

        if (isset($selector)) {

            $signature .= ':selector:' . (is_array($selector) ? implode(',', $selector) : $selector);
        }

        $vendor = $ast->vendor ?? null;

        if (isset($vendor)) {

            $signature .= ':vendor:' . $vendor;
        }

        return $signature;
    }

    /**
     * @param stdClass $ast
     * @return stdClass
     */
    protected function deduplicateRules($ast)
    {
        if (isset($ast->children)) {

            if (empty($this->options['allow_duplicate_rules']) ||
                is_array($this->options['allow_duplicate_rules'])) {

                $signature = '';
                $total = count($ast->children);

                $allowed = is_array($this->options['allow_duplicate_rules']) ? $this->options['allow_duplicate_rules'] : [];

                while ($total--) {

                    if ($total > 0) {

                        $el = $ast->children[$total];

                        $i = $total;

                        if ($el->type == 'Comment' || $el->type == 'NestingRule') {

                            continue;
                        }

                        $next = $ast->children[$total - 1];

                        while ($total > 1 && (string)$next->type == 'Comment') {

                            $next = $ast->children[--$total - 1];
                        }

                        if (!empty($allowed) &&
                            (
                                ($next->type == 'AtRule' && in_array($next->name, $allowed)) ||
                                ($next->type == 'Rule' &&
                                    array_intersect(is_array($next->selector) ? $next->selector : [$next->selector], $allowed))
                            )
                        ) {

                            continue;
                        }

                        if ($signature === '') {

                            $signature = $this->computeSignature($el);
                        }

                        $nextSignature = $this->computeSignature($next);

                        while ($next != $el && $signature == $nextSignature) {

                            array_splice($ast->children, $total - 1, 1);

                            if ($el->type != 'Declaration') {

                                $next->parent = null;
                                array_splice($el->children, 0, 0, $next->children);

                                if (isset($next->location) && isset($el->location)) {

                                    $el->location->start = $next->location->start;
                                }
                            }

                            if ($total == 1) {

                                break;
                            }

                            $next = $ast->children[--$total - 1];

                            while ($total > 1 && $next->type == 'Comment') {

                                $next = $ast->children[--$total - 1];
                            }

                            $nextSignature = $this->computeSignature($next);
                        }

                        $signature = $nextSignature;
                    }
                }
            }

            foreach ($ast->children as $key => $element) {

                $ast->children[$key] = $this->deduplicate($element);
            }
        }

        return $ast;
    }

    /**
     * @param stdClass $ast
     * @return stdClass
     */
    protected function deduplicateDeclarations($ast)
    {

        if ($this->options['allow_duplicate_declarations'] !== true && !empty($ast->children)) {

            $elements = $ast->children;
            $total = count($elements);

            $hash = [];
            $exceptions = is_array($this->options['allow_duplicate_declarations']) ? $this->options['allow_duplicate_declarations'] : !empty($this->options['allow_duplicate_declarations']);

            while ($total--) {

                $declaration = $ast->children[$total];

                if ($declaration->type == 'Comment') {

                    continue;
                }

                $signature = $this->computeSignature($declaration);

                if ($exceptions === true || isset($exceptions[$signature])) {

                    continue;
                }

                if (isset($hash[$signature])) {

                    $declaration->parent = null;
                    array_splice($ast->children, $total, 1);
                    continue;
                }

                $hash[$signature] = 1;
            }
        }

        return $ast;
    }

    /**
     * @param string $file
     * @param string $media
     * @return string
     * @throws Exception
     * @ignore
     */
    protected function getFileContent(string $file, string $media = '')
    {

        if (!preg_match('#^(https?:)?//#', $file)) {

            if (is_file($file)) {

                $content = file_get_contents($file);

                return $media === '' || $media == 'all' ? $content : '@media ' . $media . ' {' . $content . '}';
            }

            throw new Exception('File Not Found', 404);
        } else {

            $content = Helper::fetchContent($file);
        }

        if ($content === false) {

            throw new Exception(sprintf('File Not Found "%s"', $file), 404);
        }

        return $content;
    }

    /**
     *
     * @return Parser
     * @ignore
     */
    protected function getRoot(): Parser
    {

        if (is_null($this->ast)) {

            $this->ast = (object)[
                'type' => 'Stylesheet',
                'location' => (object)[
                    'start' => (object)[
                        'line' => 1,
                        'column' => 1,
                        'index' => 0
                    ],
                    'end' => (object)[
                        'line' => 1,
                        'column' => 1,
                        'index' => 0
                    ]
                ]
            ];

            if ($this->src !== '') {

                $this->ast->src = $this->src;
            }
        }

        return $this;
    }

    /**
     * @return Parser $ast
     * @throws SyntaxError
     */
    public function tokenize()
    {

        if (!isset($this->ast)) {

            $this->getRoot();
        }

        if (!isset($this->ast->children)) {

            $this->ast->children = [];
        }

        array_splice($this->ast->children, count($this->ast->children), 0, $this->getTokens());
        $this->deduplicate($this->ast);

        return $this;
    }

    /**
     * @return array
     * @throws SyntaxError
     */
    protected function getTokens(): array
    {

        $position = $this->ast->location->end;

        $i = $position->index - 1;
        $j = strlen($this->css) - 1;

        $tokens = [];

        while ($i++ < $j) {

            while ($i < $j && static::is_whitespace($this->css[$i])) {

                $this->update($position, $this->css[$i]);

                $position->index += strlen($this->css[$i++]);
            }

            if ($this->css[$i] == '/' && substr($this->css, $i + 1, 1) == '*') {

                $comment = static::match_comment($this->css, $i, $j);

                if ($comment === false) {

                    $this->handleError(sprintf('unterminated comment at %s:%s', $position->line, $position->column));
                }

                $start = clone $position;
                $this->update($position, $comment);
                $position->index += strlen($comment);

                $token = (object)[
                    'type' => 'Comment',
                    'location' => (object)[
                        'start' => $start,
                        'end' => clone $position
                    ],
                    'value' => $comment
                ];

                $token->location->start->index += $this->parentOffset;
                $token->location->end->index += $this->parentOffset;

                if ($this->src !== '') {

                    $token->src = $this->src;
                }

                $token->location->end->index = max(1, $token->location->end->index - 1);
                $token->location->end->column = max($token->location->end->column - 1, 1);

                $i += strlen($comment) - 1;
                $tokens[] = $token;
                continue;
            }

            $name = static::substr($this->css, $i, $j, ['{', ';', '}']);

            if ($name === false) {

                $name = substr($this->css, $i);
            }

            if (trim($name) === '') {

                $this->update($position, $name);
                $position->index += strlen($name);
                continue;
            }

            $char = trim(substr($name, -1));

            if (substr($name, 0, 1) != '@' &&
                $char != '{') {

                // $char === ''
                if ('' === trim($name, "; \r\n\t")) {

                    $this->update($position, $name);
                    $position->index += strlen($name);
                    $i += strlen($name) - 1;
                    continue;
                }

                $declaration = Value::split(!in_array($char, [';', '}']) ? $name : substr($name, 0, -1), ':', 2);

                if (count($declaration) < 2 || $this->ast->type == 'Stylesheet') {

                    $this->handleError(sprintf('invalid declaration %s:%s:%s "%s"', $this->src, $position->line, $position->column, $name));
                }

                $end = clone $position;

                $string = rtrim($name);
                $this->update($end, $string);
                $end->index += strlen($string);

                $declaration = (object)array_merge(
                    [
                        'type' => 'Declaration',
                        'location' => (object)[
                            'start' => clone $position,
                            'end' => $end
                        ]
                    ],
                    $this->parseVendor(trim($declaration[0])),
                    [
                        'value' => rtrim($declaration[1], "\n\r\t ")
                    ]);

                if ($this->src !== '') {

                    $declaration->src = $this->src;
                }

                if (strpos($declaration->name, '/*') !== false) {

                    $leading = [];
                    $declaration->name = trim(Value::parse($declaration->name)->
                    filter(function ($value) use (&$leading) {

                        if ($value->type == 'Comment') {

                            $leading[] = $value;
                            return false;
                        }

                        return true;
                    }));

                    if (!empty($leading)) {

                        $declaration->leadingcomments = $leading;
                    }
                }

                if (strpos($declaration->value, '/*') !== false) {

                    $trailing = [];
                    $declaration->value = Value::parse($declaration->value)->
                    filter(function ($value) use (&$trailing) {

                        if ($value->type == 'Comment') {

                            $trailing[] = $value;
                            return false;
                        }

                        return true;
                    });

                    if (!empty($trailing)) {

                        $declaration->trailingcomments = $trailing;
                    }
                }

                if (in_array($declaration->name, ['src', 'background', 'background-image'])) {

                    $declaration->value = preg_replace_callback('#(^|[\s,/])url\(\s*(["\']?)([^)\\2]+)\\2\)#', function ($matches) {

                        $file = trim($matches[3]);

                        if (strpos($file, 'data:') !== false) {

                            return $matches[0];
                        }

                        if (!preg_match('#^(/|((https?:)?//))#', $file)) {

                            $file = Helper::absolutePath($file, dirname($this->src));
                        }

                        return $matches[1] . 'url(' . $file . ')';

                    }, $declaration->value);
                }

                $tokens[] = $declaration;

                $declaration->location->start->index += $this->parentOffset;
                $declaration->location->end->index += $this->parentOffset;

                $declaration->location->end->index = max(1, $declaration->location->end->index - 1);
                $declaration->location->end->column = max($declaration->location->end->column - 1, 1);

                $this->update($position, $name);
                $position->index += strlen($name);

                $i += strlen($name) - 1;
                continue;
            }

            if ($name[0] == '@' || $char == '{') {

                if ($name[0] == '@') {

                    // at-rule
                    if (preg_match('#^@([a-z-]+)([^{;}]*)#', trim($name, ";{ \n\r\t"), $matches)) {

                        $rule = (object)array_merge([
                            'type' => 'AtRule',
                            'location' => (object)[
                                'start' => clone $position,
                                'end' => clone $position
                            ],
                            'isLeaf' => true,
                            'hasDeclarations' => $char == '{',
                        ], $this->parseVendor($matches[1]),
                            [
                                'value' => trim($matches[2])
                            ]
                        );

                        if ($rule->hasDeclarations) {

                            $rule->hasDeclarations = !in_array($rule->name, ['media', 'document', 'container', 'keyframes']);
                        }

                        if ($this->src !== '') {

                            $rule->src = $this->src;
                        }

                        if ($rule->name == 'import') {

                            preg_match('#^((url\((["\']?)([^\\3]+)\\3\))|((["\']?)([^\\6]+)\\6))(.*?$)#', $rule->value, $matches);

                            $media = trim($matches[8]);

                            if ($media == 'all') {

                                $media = '';
                            }

                            $file = empty($matches[4]) ? $matches[7] : $matches[4];

                            if (!empty($this->options['flatten_import'])) {

                                $file = Helper::absolutePath($file, dirname($this->src));

                                if ($this->src !== '' && !preg_match('#^(/|(https?:))#i', $file)) {

                                    $file = preg_replace('#' . preg_quote(Helper::getCurrentDirectory() . '/', '#') . '#', '', dirname($this->src) . '/' . $file);
                                }

                                $parser = (new self('', $this->options))->load($file);

                                if (!isset($rule->children)) {

                                    $rule->children = [];
                                }

                                $parser->parentStylesheet = $this->ast;
                                $parser->parentMediaRule = $this->parentMediaRule;
                                $rule->name = 'media';

                                if ($media === '') {

                                    unset($rule->value);
                                } else {

                                    $rule->value = $media;

                                    if ($media != 'all') {

                                        $parser->parentMediaRule = $rule;
                                    }
                                }

                                $rule->children = $parser->getRoot()->getTokens();

                                if (!empty($parser->errors)) {

                                    array_splice($this->errors, count($this->errors), 0, $parser);
                                }

                                unset($rule->isLeaf);
                            } else {

                                $rule->value = trim("\"$file\" $media");
                                unset($rule->hasDeclarations);
                            }

                        } else if ($char == '{') {

                            unset($rule->isLeaf);
                        }

                        if ($char != '{') {

                            $tokens[] = $rule;

                            $this->update($position, $name);
                            $position->index += strlen($name);

                            $rule->location->end = clone $position;
                            $rule->location->end->column = max(1, $rule->location->end->column - 1);
                            $i += strlen($name) - 1;
                            unset($rule->hasDeclarations);
                            continue;
                        }

                    } else {

                        $this->handleError(sprintf('cannot parse rule at %s:%s:%s', $this->src, $position->line, $position->column));
                    }

                    if (!empty($rule->isLeaf)) {

                        $this->update($position, $name);
                        $position->index += strlen($name);

                        $rule->location->end = clone $position;
                        $rule->location->end->index = max(1, $rule->location->end->index - 1);

                        $i += strlen($name) - 1;
                        continue;
                    }
                } else {

                    $selector = rtrim(substr($name, 0, -1));
                    $rule = (object)[

                        'type' => 'Rule',
                        'location' => (object)[

                            'start' => clone $position,
                            'end' => clone $position
                        ],
                        'selector' => $selector
                    ];

                    if ($this->src !== '') {

                        $rule->src = $this->src;
                    };

                    if (strpos($name, '/*') !== false) {

                        $leading = [];
                        $rule->selector = Value::parse($rule->selector)->
                        filter(function ($value) use (&$leading) {

                            if ($value->type == 'Comment') {

                                $leading[] = $value;
                                return false;
                            }

                            return true;
                        });

                        $rule->leading = $leading;
                    }
                }

                if ($rule->type == 'AtRule') {

                    if ($rule->name == 'nest') {

                        $rule->type = 'NestingAtRule';
                        $rule->selector = $rule->value;

                        unset($rule->name);
                        unset($rule->value);
                        unset($rule->hasDeclarations);
                    }
                }

                $this->update($rule->location->end, $name);
                $rule->location->end->index += strlen($name);

                if ($rule->type == 'Rule') {

                    if ($this->ast->type == 'Rule') {

                        $rule->selector = Value::split($rule->selector, ',');

                        foreach ($rule->selector as $selector) {

                            if (!preg_match('#^&([^A-Za-z0-9]|$)#', $selector[0])) {

                                $this->handleError(sprintf('nesting selector must start with "&" at %s:%s:%s', $rule->src, $rule->location->start->line, $rule->location->start->column));
                            }
                        }
                    }
                }

                if ($rule->type == 'AtRule' && $rule->name == 'nest') {

                    if (is_null($this->parentStylesheet) || !in_array($this->parentStylesheet->type, ['Rule', 'NestingRule', 'NestingAtRule', 'AtRule'])) {

                        $this->handleError('nesting at-rule is allowed in a rule %s:%s:%s', $rule);
                    }

                    if (!preg_match('#(^|[^A-Za-z0-9])&([^A-Za-z0-9]|$)#', $rule->name)) {

                        $this->handleError(sprintf('nesting at-rule must contain "&" %s:%s:%s', $rule->src, $rule->location->start->line, $rule->location->start->column));
                    }
                }

                $tokens[] = $rule;

                $body = static::_close($this->css, '}', '{', $i + strlen($name), $j);

                $parser = new self(substr($body, 0, -1), $this->options);
                $parser->src = $this->src;
                $parser->ast = $rule;
                $parser->parentMediaRule = $this->parentMediaRule;

                if ($rule->type == 'AtRule' && $rule->name == 'media' &&
                    isset($rule->value) && $rule->value != '' && $rule->value != 'all') {

                    if (isset($parser->parentMediaRule)) {

                        $parser->parentMediaRule->type = 'NestingMediaRule';
                    }

                    $parser->parentMediaRule = $rule;
                }

                $parser->parentStylesheet = $rule->type == 'Rule' ? $rule : $this->ast;
                $parser->parentOffset = $rule->location->end->index + $this->parentOffset;

                if (($this->parentStylesheet->type ?? null) == 'Rule') {

                    $this->parentStylesheet->type = 'NestingRule';
                }

                $rule->location->end->index = 0;
                $rule->location->end->column = max($rule->location->end->column - 1, 1);

                $parser->ast->children = $parser->getTokens();

                $string = $name . $body;
                $this->update($position, $string);
                $position->index += strlen($string);

                $rule->location->end = clone $position;

                $i += strlen($string) - 1;
                $rule->location->end->index = max(1, $rule->location->end->index - 1);
                $rule->location->end->column = max($rule->location->end->column - 1, 1);

                if (!empty($parser->errors)) {

                    array_splice($this->errors, count($this->errors), 0, $parser);
                }
//                continue;
            }
        }

        $this->ast->location->end->index = max(1, $this->ast->location->end->index - 1);
        $this->ast->location->end->column = max($this->ast->location->end->column - 1, 1);

        return $tokens;
    }

    /**
     * @throws SyntaxError
     * @throws Exception
     * @ignore
     */
    protected function doParse()
    {

        $this->errors = [];
        $this->css = rtrim($this->css);

        return $this->tokenize();
    }

    /**
     * @param stdClass $position
     * @param string $string
     * @return stdClass
     * @ignore
     */
    protected function update($position, string $string)
    {

        $j = strlen($string);

        for ($i = 0; $i < $j; $i++) {

            if ($string[$i] == PHP_EOL) {

                $position->line++;
                $position->column = 1;
            } else {

                $position->column++;
            }
        }

        return $position;
    }

    /**
     * @param string $str
     * @return array
     * @ignore
     */
    protected function parseVendor($str)
    {

        if (preg_match('/^(-([a-zA-Z]+)-(\S+))/', trim($str), $match)) {

            return [

                'name' => $match[3],
                'vendor' => $match[2]
            ];
        }

        return ['name' => $str];
    }

    /**
     * @param string $message
     * @param int $error_code
     * @throws SyntaxError
     */
    protected function handleError(string $message, int $error_code = 400)
    {

        $error = new SyntaxError($message, $error_code);

        if (!$this->options['capture_errors']) {

            throw $error;
        }

        $this->errors[] = $error;
    }

    /**
     * return parse errors
     * @return Exception[]
     */
    public function getErrors()
    {

        return $this->errors;
    }

    public function __toString()
    {

        if (!isset($this->ast)) {

            $this->getAst();
        }

        if (isset($this->ast)) {

            return (new Renderer())->renderAst($this->ast);
        }

        return '';
    }
}