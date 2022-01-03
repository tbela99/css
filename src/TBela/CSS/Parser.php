<?php

namespace TBela\CSS;

use Closure;
use Exception;
use stdClass;

use TBela\CSS\Event\EventTrait;
use TBela\CSS\Interfaces\ParsableInterface;
use TBela\CSS\Interfaces\RuleListInterface;
use TBela\CSS\Interfaces\ValidatorInterface;
use TBela\CSS\Parser\Helper;
use TBela\CSS\Parser\Lexer;
use TBela\CSS\Parser\ParserTrait;
use TBela\CSS\Parser\SyntaxError;
use TBela\CSS\Value\Set;
use function preg_replace_callback;
use function substr;

/**
 * Css Parser
 * @package TBela\CSS
 * ok */
class Parser implements ParsableInterface
{

    use ParserTrait;

    /**
     * @var ValidatorInterface[]
     */
    protected static array $validators = [];

    protected array $context = [];
    protected Lexer $lexer;

    protected array $errors = [];

    protected ?stdClass $ast = null;

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
        $this->setOptions($options);
        $this->lexer = $this->createLexer();

        if ($css !== '') {

            $this->setContent($css);
        }
    }

    /**
     * @return Lexer
     */
    protected function createLexer(): Lexer
    {

        if (!isset($this->lexer)) {

            $this->lexer = (new Lexer('', $this->getContext(), $this->options))->
            on('enter', Closure::fromCallable([$this, 'enterNode']))->
            on('exit', Closure::fromCallable([$this, 'exitNode']))->
            on('replace', Closure::fromCallable([$this, 'replaceNode']))->
            on('remove', Closure::fromCallable([$this, 'removeNode']))->
            on('error', Closure::fromCallable([$this, 'onError']));
        }

        return $this->lexer;
    }

    protected function validate(object $token, object $parentRule, object $parentStylesheet)
    {

        if (!isset(static::$validators[$token->type])) {

            $type = static::class . '\\Validator\\' . $token->type;

            if (class_exists($type)) {

                static::$validators[$token->type] = new $type;
            }
        }

        if (isset(static::$validators[$token->type])) {

            return static::$validators[$token->type]->validate($token, $parentRule, $parentStylesheet);
        }

        return ValidatorInterface::VALID;
    }

    protected function getContext()
    {

        return end($this->context) ?: $this->ast;
    }


    protected function pushContext(object $context)
    {

        $this->context[] = $context;
    }

    protected function popContext()
    {

        array_pop($this->context);;
    }

    /**
     * @throws SyntaxError
     */
    protected function enterNode($token, $parentRule, $parentStylesheet)
    {

        if ($token->type != 'Comment' && strpos($token->type, 'Invalid') !== 0) {

            $property = property_exists($token, 'name') ? 'name' : (property_exists($token, 'selector') ? 'selector' : null);

            if ($property) {

                if (strpos($token->{$property}, '/*') !== false ||
                    strpos($token->{$property}, '<!--') !== false) {

                    $leading = [];
                    $token->{$property} = trim(Value::parse($token->{$property})->
                    filter(function ($value) use (&$leading, $token) {

                        if ($value->type == 'Comment') {

                            if (substr($value, 0, 4) == '<!--') {

                                $this->handleError(sprintf('CDO token not allowed here %s %s:%s:%s', $token->type, $token->src ?? '', $token->location->start->line, $token->location->start->column));
                            } else {

                                $leading[] = $value;
                            }

                            return false;
                        }

                        return true;
                    }));

                    if (!empty($leading)) {

                        $token->leadingcomments = $leading;
                    }
                }
            }

            if (property_exists($token, 'value')) {
                if (strpos($token->value, '/*') !== false ||
                    strpos($token->value, '<!--') !== false) {

                    $trailing = [];
                    $token->value = Value::parse($token->value)->
                    filter(function ($value) use (&$trailing, $token) {

                        if ($value->type == 'Comment') {

                            if (substr($value, 0, 4) == '<!--') {

                                $this->handleError(sprintf('CDO token not allowed here %s %s:%s:%s', $token->type, $token->src ?? '', $token->location->start->line, $token->location->start->column));
                            } else {

                                $trailing[] = $value;
                            }

                            return false;
                        } else if ($value->type == 'invalid-comment') {

                            return false;
                        }

                        return true;
                    });

                    if (!empty($trailing)) {

                        $token->trailingcomments = $trailing;
                    }
                }
            }
        }

        $context = $this->getContext();
        $status = $this->doValidate($token, $context, $parentStylesheet);

        if ($status == ValidatorInterface::VALID) {

            $context->children[] = $token;

            if (in_array($token->type, ['Rule', 'NestingRule', 'NestingAtRule', 'NestingMediaRule']) || ($token->type == 'AtRule' && empty($token->isLeaf))) {

                $this->pushContext($token);
            }
        }

        return $status;
    }

    /**
     * @param object $token
     * @param object $oldToken
     * @param object $parentStylesheet
     * @return int
     * @throws SyntaxError
     */
    protected function replaceNode(object $token, object $oldToken, object $parentStylesheet)
    {

        $context = $this->getContext();
        $status = $this->doValidate($token, $context, $parentStylesheet);

        if ($status == ValidatorInterface::VALID && $token != $oldToken) {

            if (!empty($context->children)) {

                $i = count($context->children);

                while ($i--) {

                    if ($context->children[$i] == $oldToken) {

                        array_splice($context->children, $i, 1, [$token]);
                        break;
                    }
                }
            }
        }

        if ($status == ValidatorInterface::VALID && in_array($token->type, ['Rule', 'NestingRule', 'NestingAtRule', 'NestingMediaRule']) || ($token->type == 'AtRule' && empty($token->isLeaf))) {

            if (!in_array($token, $this->context)) {

                $this->pushContext($token);
            }
        }

        return $status;
    }


    protected function removeNode($token)
    {

        $context = $this->getContext();

        if (!empty($context->children)) {

            $i = count($context->children);

            while ($i--) {

                if ($context->children[$i] == $token) {

                    array_splice($context->children, $i, 1);
                    break;
                }
            }
        }
    }

    protected function exitNode(object $token)
    {

        if (in_array($token->type, ['Rule', 'NestingRule', 'NestingAtRule', 'NestingMediaRule']) || ($token->type == 'AtRule' && empty($token->isLeaf))) {

            $this->popContext();
        }
    }

    /**
     * @throws Exception
     */
    protected function onError($token, Exception $exception)
    {

        if (!$this->options['capture_errors']) {

            throw $exception;
        }

        $this->errors[] = $exception;
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

        if (!isset($this->ast)) {

            $this->getAst();
        }

        if (!isset($parser->ast)) {

            $parser->getAst();
        }

        array_splice($this->ast->children, count($this->ast->children), 0, $parser->ast->children);
        array_splice($this->errors, count($this->errors), 0, $parser->errors);

        $this->deduplicate($this->ast);
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

        return $this->merge(new self($css, $this->options));
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

        $this->ast = null;
        $this->lexer = $this->createLexer()->setContent($css);

        return $this;
    }

    /**
     * load css content from a file
     * @param string $file
     * @param string $media
     * @return Parser
     * @throws Exceptions\IOException
     */

    public function load($file, $media = '')
    {

        $this->lexer = $this->createLexer()->load($file, $media);

        $this->ast = null;
        $this->errors = [];
        $this->context = [];

        return $this;
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

                if ($key == 'allow_duplicate_declarations') {

                    if (is_string($this->options[$key])) {

                        $this->options[$key] = [$this->options[$key]];
                    }

                    if (is_array($this->options[$key])) {

                        $this->options[$key] = array_flip($this->options[$key]);
                    }
                } else if ($key == 'allow_duplicate_rules' && is_string($v)) {

                    $this->options[$key] = [$v];
                } else {

                    $this->options[$key] = $options[$key];
                }

                if ($key == 'allow_duplicate_rules' && is_array($this->options[$key]) && !in_array('font-face', $this->options[$key])) {

                    $this->options[$key][] = 'font-face';
                }
            }
        }

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

            $this->getAst();
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

            $this->ast = $this->lexer->createContext();
            $this->lexer->setOptions($this->options)->setContext($this->ast)->tokenize();
            $this->deduplicate($this->ast);
        }

        return $this->ast;
    }

    /**
     * @param stdClass $ast
     * @return stdClass
     */
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

            $signature .= ':value:' . (is_string($value) ? Value::parse($value, $name) : $value)->getHash();
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
                        if ($el->type == 'Comment') {

                            continue;
                        }

                        if ($el->type != 'Rule') {

                            break;
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

                    if (isset($declaration->parent)) {

                        $declaration->parent = null;
                    }

                    array_splice($ast->children, $total, 1);
                    continue;
                }

                $hash[$signature] = 1;
            }
        }

        return $ast;
    }

    /**
     * @param string $message
     * @param int $error_code
     * @return SyntaxError
     * @throws SyntaxError
     */
    protected function handleError(string $message, int $error_code = 400)
    {

        $error = new SyntaxError($message, $error_code);

        if (!$this->options['capture_errors']) {

            throw $error;
        }

        $this->errors[] = $error;

        return $error;
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

        try {

            if (!isset($this->ast)) {

                $this->getAst();
            }

            if (isset($this->ast)) {

                return (new Renderer())->renderAst($this->ast);
            }
        } catch (Exception $ex) {

            error_log($ex);
        }

        return '';
    }

    /**
     * @param object $token
     * @param object $context
     * @param object $parentStylesheet
     * @return int
     * @throws SyntaxError
     */
    protected function doValidate(object $token, object $context, object $parentStylesheet): int
    {
        $status = $this->validate($token, $context, $parentStylesheet);

        if ($status == ValidatorInterface::REJECT) {

            $this->handleError(sprintf('invalid token %s at %s:%s:%s', $token->type, $token->src ?? '', $token->location->start->line, $token->location->start->column));
        }

        return $status;
    }
}