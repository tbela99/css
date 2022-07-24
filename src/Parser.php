<?php

namespace TBela\CSS;

use Closure;
use Exception;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
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

    protected ?object $ast = null;

    // 65kb
    const CSS_MAX_SIZE = 66560;

    /**
     * @var array
     * @ignore
     */
    protected array $options = [
        'capture_errors' => true,
        'flatten_import' => false,
        'allow_duplicate_rules' => ['font-face'], // set to true for speed
        'allow_duplicate_declarations' => true,
        'multi_processing' => true,
        'children_process' => 20,
        'ast_src' => '',
        'ast_position_line' => 1,
        'ast_position_column' => 1,
        'ast_position_index' => 0
    ];

    /**
     * last deduplicated rule index
     * @ignore
     * @var int|null
     */
    protected ?int $lastDedupIndex = null;

    /**
     * @var array
     * @ignore
     */
    protected array $queue = [];

    /**
     * @var string[]
     * @ignore
     */
    protected array $data = [];

    /**
     * @var Process[]
     * @ignore
     */
    protected array $actives = [];

    /**
     * active processes
     * @ignore
     * @var int
     */
    protected int $running = 0;
    protected int $sleepTime = 30;

    /**
     * @var string serialize | json
     */
    protected string $format = 'serialize';

    /**
     * Parser constructor.
     * @param string $css
     * @param array $options
     * @throws IOException
     * @throws SyntaxError
     */
    public function __construct(string $css = '', array $options = [])
    {

        $this->setOptions($options);
        $this->lexer = (new Lexer())->
        setParentOffset((object)[

            'line' => $this->options['ast_position_line'],
            'column' => $this->options['ast_position_column'],
            'index' => $this->options['ast_position_index'],
            'src' => $this->options['ast_src']
        ])->
        on('enter', Closure::fromCallable([$this, 'enterNode']))->
        on('exit', Closure::fromCallable([$this, 'exitNode']));

        if ($css !== '') {

            $this->setContent($css);
        }
    }

    protected function enQueue($index, $src, $buffer, $position)
    {

        $this->queue[] = [$index, $src, $buffer, $position];

        if ($this->running >= $this->options['children_process']) {

            return;
        }

        $this->dequeue();
    }

    public function dequeue()
    {

        $data = array_shift($this->queue);

        if (empty($data)) {

            return $this;
        }

        list($index, $src, $buffer, $position) = $data;

        $phpPath = (new PhpExecutableFinder())->find();

        $cmd = [
            $phpPath,
            '-f',
            __DIR__ . '/../cli/css-parser',
            '--',
            '-ac',
            sprintf('--format=%s', $this->format),
            '--parse-multi-processing=off',
            sprintf('--parse-ast-src=%s', $src),
            sprintf('--parse-ast-position-index=%s', $position->index),
            sprintf('--parse-ast-position-line=%s', $position->line),
            sprintf('--parse-ast-position-column=%s', $position->column),
            sprintf('--capture-errors=%s', $this->options['capture_errors']),
            sprintf('--flatten-import=%s', $this->options['flatten_import']),
            sprintf('--parse-allow-duplicate-declarations=%s', (int)$this->options['allow_duplicate_declarations'])
        ];

//        fwrite(STDERR, "\n" . implode(' ', $cmd) . "\n");

        $process = new Process($cmd);

        $this->running++;
        $this->data[$index] = '';

        $process->setInput($buffer);

        $this->actives[$index] = $process;

        $process->start(function ($type, $buffer) use ($index) {

            if ($type === Process::OUT) {

                $this->data[$index] .= $buffer;
            }
        });
    }

    public function slice($css, $position): \Generator
    {

        $i = $position->index - 1;
        $j = strlen($css) - 1;

        $buffer = '';

        while ($i++ < $j) {

            $string = Parser::substr($css, $i, $j, ['{']);

            if ($string === false) {

                $buffer = substr($css, $i);

                yield [$buffer, clone $position];

                $this->update($position, $buffer);
                $position->index += strlen($buffer);
                break;
            }

            $string .= Parser::_close($css, '}', '{', $i + strlen($string), $j);
            $buffer .= $string;

            if (strlen($buffer) >= static::CSS_MAX_SIZE) {

                $k = 0;
                while (static::is_whitespace($buffer[$k])) {

                    $k++;
                }

                if ($k > 0) {

                    $this->update($position, substr($buffer, 0, $k));
                    $position->index += $k;
                    $buffer = substr($buffer, $k);
                    $i += $k - 1;
                }

                yield [$buffer, clone $position];

                $this->update($position, $buffer);
                $position->index += strlen($buffer);
                $buffer = '';
            }

            $i += strlen($string) - 1;
        }

        if ($buffer) {

            $k = 0;
            while (static::is_whitespace($buffer[$k])) {

                $k++;
            }

            if ($k > 0) {

                $this->update($position, substr($buffer, 0, $k));
                $position->index += $k;
                $buffer = substr($buffer, $k);
            }
        }

        if (trim($buffer) !== '') {

            yield [$buffer, clone $position];
            $this->update($position, $buffer);
            $position->index += strlen($buffer);
        }
    }

    /**
     * @param string $event parse event name in ['enter', 'exit']
     * @param callable $callable
     * @return Parser
     */
    public function on(string $event, callable $callable): static
    {

        $this->lexer->on($event, $callable);
        return $this;
    }

    /**
     * @param string $event parse event name in ['enter', 'exit', 'start', 'end']
     * @param callable $callable
     * @return Parser
     */
    public function off(string $event, callable $callable): static
    {

        $this->lexer->off($event, $callable);
        return $this;
    }

    /**
     * @param string $content
     * @param object $root
     * @param string $file
     * @return void
     */
    protected function stream(string $content, object $root, string $file): void
    {
        foreach ($this->slice($content, $root->location->end) as $index => $data) {

            $this->enQueue($index, $file, $data[0], $data[1]);
        }

        while ($this->running > 0) {

            foreach ($this->actives as $key => $process) {

                if (!$process->isRunning()) {

                    $this->running--;
                    unset($this->actives[$key]);
                }
            }

            usleep($this->sleepTime);
        }

        foreach ($this->data as $payload) {

            $token = $this->format == 'serialize' ? unserialize($payload) : json_decode($payload);

            if (!empty($token->children)) {

                if (!isset($root->children)) {

                    $root->children = [];
                }

                array_splice($root->children, count($root->children), 0, $token->children);
            }
        }

        $this->data = [];
    }

    /**
     * @param Exception $error
     * @return void
     */
    protected function emit(Exception $error): void
    {
        $this->lexer->emit('error', $error);
    }

    /**
     * parse css file and append to the existing AST
     * @param string $file
     * @param string $media
     * @return Parser
     * @throws Exception
     */
    public function append(string $file, string $media = ''): static
    {

        $file = Helper::absolutePath($file, Helper::getCurrentDirectory());

        if (!preg_match('#^(https?:)?//#', $file) && is_file($file)) {

            $content = file_get_contents($file);

        } else {

            $content = Helper::fetchContent($file);
        }

        if ($content === false) {

            throw new IOException(sprintf('File Not Found "%s" => \'%s:%s:%s\'', $file, $context->location->src ?? null, $context->location->end->line ?? null, $context->location->end->column ?? null), 404);
        }

        $rule = null;

        $newContext = $this->lexer->createContext();
        $newContext->src = $file;

        if (is_null($this->ast)) {

            $this->ast = $newContext;
        }

        if ($media !== '' && $media != 'all') {

            $rule = (object)[

                'type' => 'AtRule',
                'name' => 'media',
                'value' => Value::parse($media, null, true, '', '', true)
            ];

            $rule->location = (object)[
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
            ];

            $rule->src = $file;

            $this->ast->children[] = $rule;
            $this->pushContext($rule);
        }

        if ($this->options['multi_processing'] && strlen($content) > static::CSS_MAX_SIZE) {

            $root = $this->getContext();

            $this->stream($content, $root, $file);
        }

        else {

            $this->lexer->setContent($content)->setContext($newContext)->tokenize();
        }

        if ($rule) {

            $this->popContext();
        }

        return $this;
    }

    /**
     * parse css and append to the existing AST
     * @param string $css
     * @param string $media
     * @return Parser
     * @throws SyntaxError|IOException|Exception
     */
    public function appendContent(string $css, string $media = ''): static
    {
        if ($media !== '' && $media != 'all') {

            $css = '@media ' . $media . ' { ' . rtrim($css) . ' }';
        }

        if (!$this->ast) {

            $this->ast = $this->lexer->createContext();

            $this->lexer->setContext($this->ast);
        } else {

            $this->lexer->setContext($this->lexer->createContext());
        }

        $this->lexer->
        setContent($css)->
        tokenize();

        return $this;
    }

    /**
     * @param Parser $parser
     * @return Parser
     * @throws SyntaxError
     */
    public function merge(Parser $parser): static
    {

        assert($parser instanceof self);

        $this->getAst();
        $parser->getAst();

        if (!isset($this->ast->children)) {

            $this->ast->children = [];
        }

        if (isset($parser->ast->children)) {

            array_splice($this->ast->children, count($this->ast->children), 0, $parser->ast->children);
        }

        array_splice($this->errors, count($this->errors), 0, $parser->errors);
        return $this;
    }

    /**
     * set css content
     * @param string $css
     * @param string $media
     * @return Parser
     * @throws IOException
     * @throws SyntaxError
     */
    public function setContent(string $css, string $media = ''): static
    {

        if ($media !== '' && $media != 'all') {

            $css = '@media ' . $media . '{ ' . rtrim($css) . ' }';
        }

        $this->reset()->appendContent($css);

        return $this;
    }

    protected function reset(): static
    {

        $this->ast = null;
        $this->errors = [];
        $this->context = [];
        $this->lastDedupIndex = null;

        return $this;
    }

    /**
     * load css content from a file
     * @param string $file
     * @param string $media
     * @return Parser
     * @throws Exceptions\IOException|Exception
     */

    public function load(string $file, string $media = ''): static
    {

        return $this->reset()->append($file, $media);
    }

    /**
     * set the parser options
     * @param array $options
     * @return Parser
     */
    public function setOptions(array $options): static
    {

        foreach ($this->options as $key => $v) {

            if (isset($options[$key])) {

                if ($key == 'allow_duplicate_declarations') {

                    if (is_string($options[$key])) {

                        $this->options[$key] = [$options[$key]];
                    } else if (is_array($options[$key])) {

                        $this->options[$key] = array_flip($options[$key]);
                    } else {

                        $this->options[$key] = $options[$key];
                    }

                } else if ($key == 'allow_duplicate_rules' && is_string($options[$key])) {

                    $this->options[$key] = [$options[$key]];
                } else {

                    $this->options[$key] = $options[$key];
                }

                if ($key == 'allow_duplicate_rules' && is_array($options[$key]) && !in_array('font-face', $options[$key])) {

                    $this->options[$key] = $options[$key];
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
    public function parse(): ?RuleListInterface
    {


        $this->getAst();


        return Element::getInstance($this->ast);
    }

    /**
     * @inheritDoc
     * @throws SyntaxError|Exception
     */
    public function getAst(): ?object
    {

        if (is_null($this->ast)) {

            $this->ast = $this->lexer->createContext();
            $this->lexer->setContext($this->ast)->tokenize();
        }

        if (!empty($this->ast->children)) {

            $min = min($this->lastDedupIndex, count($this->ast->children) - 1);


            if ($this->options['flatten_import']) {

                $i = count($this->ast->children);

                while ($min < $i--) {


                    if ($this->ast->children[$i]->type != 'AtRule' || $this->ast->children[$i]->name != 'import') {

                        continue;
                    }

                    $token = $this->ast->children[$i];

                    preg_match('#^((["\']?)([^\\2]+)\\2)(.*?$)#', is_array($token->value) ? Value::renderTokens($token->value) : $token->value, $matches);

                    $media = trim($matches[4] ?? '');

                    if ($media == 'all') {

                        $media = '';
                    }

                    $file = Helper::absolutePath($matches[3], dirname($token->src ?? ''));


                    if ($media === '') {


                    } else {

                        $token->value = $media;
                    }

                    $this->pushContext($token);
                    $this->append($file, $media);


                    $this->popContext();

                    if ($media === '') {

                        array_splice($this->ast->children, $i, 1, $token->children ?? []);
                    }


                }


            }

            $this->deduplicate($this->ast, $this->lastDedupIndex);

            $this->lastDedupIndex = max(0, count($this->ast->children) - 2);
        }

        return $this->ast;
    }

    /**
     * @param object $ast
     * @param int|null $index
     * @return object
     */
    public function deduplicate(object $ast, ?int $index = null): object
    {

        if ($this->options['allow_duplicate_rules'] !== true ||
            $this->options['allow_duplicate_declarations'] !== true) {

            switch ($ast->type) {

                case 'Stylesheet':

                    return $this->deduplicateRules($ast, $index);

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
     * @param object $ast
     * @return string
     * @ignore
     */
    protected function computeSignature(object $ast): string
    {

        $signature = 'type:' . $ast->type;

        $name = $ast->name ?? null;

        if (isset($name)) {

            $signature .= ':name:' . $name;
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
     * @param object $ast
     * @param int|null $index
     * @return object
     */
    protected function deduplicateRules(object $ast, ?int $index = null): object
    {
        if (isset($ast->children)) {

            if (empty($this->options['allow_duplicate_rules']) ||
                is_array($this->options['allow_duplicate_rules'])) {

                $signature = '';
                $total = count($ast->children);

                $allowed = is_array($this->options['allow_duplicate_rules']) ? $this->options['allow_duplicate_rules'] : [];

                $min = (int)$index;

                while ($total-- > $min) {

                    if ($total > 0) {

                        $el = $ast->children[$total];
                        if ($el->type == 'Comment') {

                            continue;
                        }

                        if ($el->type != 'Rule') {

                            break;
                        }

                        $next = $ast->children[$total - 1];

                        while ($total > 1 && $next->type == 'Comment') {

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

                                if (isset($next->children)) {

                                    if (!isset($el->children)) {

                                        $el->children = [];
                                    }

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
     * @param object $ast
     * @return object
     */
    protected function deduplicateDeclarations(object $ast): object
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
    public function getErrors(): array
    {

        return $this->errors;
    }

    /**
     * @param string $message
     * @param int $error_code
     * @return SyntaxError
     * @throws SyntaxError
     */
    protected function handleError(string $message, int $error_code = 400): SyntaxError
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
    protected function validate(object $token, object $parentRule, object $parentStylesheet): int
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
     * @throws SyntaxError
     * @ignore
     */
    protected function getContext(): ?object
    {

        return end($this->context) ?: ($this->ast ?? $this->getAst());
    }

    /**
     * push the current parent node
     * @param object $context
     * @return void
     * @ignore
     */
    protected function pushContext(object $context): void
    {

        $this->context[] = $context;
    }

    /**
     * pop the current parent node
     * @return void
     * @ignore
     */
    protected function popContext(): void
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
    protected function enterNode(object $token, object $parentRule, object $parentStylesheet): int
    {

        if ($token->type != 'Comment' && !str_starts_with($token->type, 'Invalid')) {

            $hasCdoCdc = false;

            if (!empty($token->leadingcomments)) {

                $i = count($token->leadingcomments);

                while ($i--) {

                    if (str_starts_with($token->leadingcomments[$i], '<!--')) {

                        $hasCdoCdc = true;
                        array_splice($token->leadingcomments, $i, 1);
                    }
                }
            }

            if (!empty($token->trailingcomments)) {

                $i = count($token->trailingcomments);

                while ($i--) {

                    if (str_starts_with($token->trailingcomments[$i], '<!--')) {

                        $hasCdoCdc = true;
                        array_splice($token->trailingcomments, $i, 1);
                    }
                }
            }

            if ($hasCdoCdc) {

                $this->handleError(sprintf('CDO token not allowed here %s %s:%s:%s', $token->type, $token->src ?? '', $token->location->start->line, $token->location->start->column));
            }
        }

        $context = $this->getContext();
        $status = $this->doValidate($token, $context, $parentStylesheet);

        if ($status == ValidatorInterface::VALID) {

            $context->children[] = $token;

//            if ($token->type == 'AtRule' && $token->name == 'import') {
//
//                $this->imports[] = $token;
//            }

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
     * @throws SyntaxError
     * @ignore
     */
    protected function exitNode(object $token): void
    {

        if (in_array($token->type, ['Rule', 'NestingRule', 'NestingAtRule', 'NestingMediaRule']) || ($token->type == 'AtRule' && empty($token->isLeaf))) {

            $this->popContext();
        }

        if (
            in_array($token->type, ['AtRule', 'NestingMediaRule']) &&
            $token->name == 'media' &&
            (
                empty($token->value) ||
                (
                    count($token->value) == 1 &&
                    ($token->value[0]->value ?? '') == 'all'
                )
            )
        ) {

            $context = $this->getContext();

            array_pop($context->children);
            array_splice($context->children, count($context->children), 0, $token->children);

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

            $this->getAst();

            if (isset($this->ast)) {

                return (new Renderer())->renderAst($this->ast);
            }

        } catch (Exception $ex) {

            fwrite(STDERR, $ex);
            error_log($ex);
        }

        return '';
    }
}