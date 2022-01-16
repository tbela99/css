<?php

namespace TBela\CSS;

use Closure;
use Exception;
use stdClass;

use TBela\CSS\Exceptions\IOException;
use TBela\CSS\Interfaces\ParsableInterface;
use TBela\CSS\Interfaces\RuleListInterface;
use TBela\CSS\Interfaces\ValidatorInterface;
use TBela\CSS\Parser\Helper;
use TBela\CSS\Parser\Lexer;
use TBela\CSS\Parser\ParserTrait;
use TBela\CSS\Parser\SyntaxError;
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
    protected ?string $error;

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
     * @var array<string, callable>
     */
    protected array $event_handlers = [];

    /**
     * Parser constructor.
     * @param string $css
     * @param array $options
     */
    public function __construct($css = '', array $options = [])
    {

        $this->setOptions($options);
        $this->lexer = (new Lexer())->
        on('enter', Closure::fromCallable([$this, 'enterNode']))->
        on('exit', Closure::fromCallable([$this, 'exitNode']));

        if ($css !== '') {

            $this->setContent($css);
        }
    }

    /**
     * @param string $event parse event name in ['enter', 'exit'', 'start', 'end']
     * @param callable $callable
     * @return $this
     */
    public function on($event, callable $callable)
    {

        $this->lexer->on($event, $callable);
        $this->event_handlers[$event][] = $callable;

        return $this;
    }

    /**
     * @param string $event parse event name in ['enter', 'exit', 'start', 'end']
     * @param callable $callable
     * @return $this
     */
    public function off($event, callable $callable)
    {

        if (isset($this->event_handlers[$event])) {

            $this->lexer->off($event, $callable);

            foreach ($this->event_handlers as $key => $handler) {

                if ($handler == $callable) {

                    array_splice($this->event_handlers[$event], $key, 1);
                    break;
                }
            }
        }

        return $this;
    }


    /**
     * @param Exception $error
     * @return void
     */
    protected function emit(Exception $error): void
    {
        if (!empty($this->event_handlers['error'])) {

            foreach ($this->event_handlers['error'] as $handler) {

                call_user_func($handler, $error);
            }
        }
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
        $this->errors = [];
        $this->context = [];
        $this->lexer->setContent($css);

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

        $this->lexer->load($file, $media);

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
     * @throws Exceptions\IOException
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
     * @throws Exceptions\IOException
     */
    public function getAst()
    {

        if (is_null($this->ast)) {

            $this->ast = $this->lexer->createContext();
            $this->lexer->setContext($this->ast)->tokenize();

            if ($this->options['flatten_import'] && !empty($this->ast->children)) {

                $files = [];
                $removed = [];

                $i = count($this->ast->children);

                while ($i--) {

                    try {

                        if ($this->ast->children[$i]->type != 'AtRule' || $this->ast->children[$i]->name != 'import') {

                            continue;
                        }

                        $token = $this->ast->children[$i];

                        preg_match('#^((["\']?)([^\\2]+)\\2)(.*?$)#', $token->value, $matches);

                        $media = trim($matches[4] ?? '');

                        if ($media == 'all') {

                            $media = '';
                        }

                        $file = Helper::absolutePath($matches[3], dirname($token->src ?? ''));

                        $parser = (new static)->load($file);
                        $parser->event_handlers = $this->event_handlers;
                        $parser->options = $this->options;

                        foreach ($this->event_handlers as $event => $handlers) {

                            foreach ($handlers as $handler) {

                                $parser->lexer->on($event, $handler);
                            }
                        }

                        $parser->getAst();

                        if (!empty($parser->errors)) {

                            array_splice($this->errors, count($this->errors), $parser->errors);
                        }

                        $token->name = 'media';

                        if ($media === '') {

                            unset($token->value);
                        } else {

                            $token->value = $media;
                        }

                        $token->children = $parser->ast->children ?? [];
                        $token->hasDeclaration = true;

                        unset($token->isLeaf);
                        array_shift($this->imports);
                    } catch (IOException $e) {

                        if (empty($this->options['capture_errors'])) {

                            throw $e;
                        }

                        $this->emit($e);
                        $this->errors[] = $e;
                    }
                }

                if (isset($this->ast->children)) {

                    $i = count($this->ast->children);

                    while ($i--) {

                        if ($this->ast->children[$i]->type == 'AtRule' &&
                            $this->ast->children[$i]->name == 'media' &&
                            !isset($this->ast->children[$i]->value)) {

                            array_splice($this->ast->children, $i, 1, $this->ast->children[$i]->children ?? []);
                        }
                    }
                }
            }

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

                                if (isset($el->children)) {

                                    array_splice($el->children, 0, 0, $next->children);
                                }

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
     * return parse errors
     * @return Exception[]
     */
    public function getErrors()
    {

        return $this->errors;
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

        $this->emit($error);

        $this->errors[] = $error;

        return $error;
    }

    /**
     * syntax validation
     * @param object $token
     * @param object $parentRule
     * @param object $parentStylesheet
     * @return int
     * @ignore
     */
    protected function validate(object $token, object $parentRule, object $parentStylesheet)
    {

        if (!isset(static::$validators[$token->type])) {

            $type = static::class . '\\Validator\\' . $token->type;

            if (class_exists($type)) {

                static::$validators[$token->type] = new $type;
            }
        }

        $this->error = null;

        if (isset(static::$validators[$token->type])) {

            $result = static::$validators[$token->type]->validate($token, $parentRule, $parentStylesheet);
            $this->error = static::$validators[$token->type]->getError();
            return $result;
        }

        return ValidatorInterface::VALID;
    }

    /**
     * get the current parent node
     * @return object|null
     * @ignore
     */
    protected function getContext()
    {

        return end($this->context) ?: $this->ast;
    }

    /**
     * push the current parent node
     * @param object $context
     * @return void
     * @ignore
     */
    protected function pushContext(object $context)
    {

        $this->context[] = $context;
    }

    /**
     * pop the current parent node
     * @return void
     * @ignore
     */
    protected function popContext()
    {

        array_pop($this->context);;
    }

    /**
     * parse event handler
     * @param object $token
     * @param object $parentRule
     * @param object $parentStylesheet
     * @return int
     * @throws SyntaxError
     * @ignore
     */
    protected function enterNode(object $token, object $parentRule, object $parentStylesheet)
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

            if ($token->type == 'AtRule' && $token->name == 'import') {

                $this->imports[] = $token;
            }

            if (in_array($token->type, ['Rule', 'NestingRule', 'NestingAtRule', 'NestingMediaRule']) || ($token->type == 'AtRule' && empty($token->isLeaf))) {

                $this->pushContext($token);
            }
        }

        return $status;
    }

    /**
     * parse event handler
     * @param object $token
     * @return void
     * @ignore
     */
    protected function exitNode(object $token)
    {

        if (in_array($token->type, ['Rule', 'NestingRule', 'NestingAtRule', 'NestingMediaRule']) || ($token->type == 'AtRule' && empty($token->isLeaf))) {

            $this->popContext();
        }
    }

    /**
     * perform the syntax validation
     * @param object $token
     * @param object $context
     * @param object $parentStylesheet
     * @return int
     * @throws SyntaxError
     * @ignore
     */
    protected function doValidate(object $token, object $context, object $parentStylesheet): int
    {
        $status = $this->validate($token, $context, $parentStylesheet);

        if ($status == ValidatorInterface::REJECT) {

            $this->handleError(sprintf("%s: %s at %s:%s:%s\n", $token->type, $this->error, $token->src ?? '', $token->location->start->line, $token->location->start->column));
        }

        return $status;
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
}