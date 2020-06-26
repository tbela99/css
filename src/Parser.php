<?php

namespace TBela\CSS;

use Exception;
use \stdClass;
use TBela\CSS\Parser\Helper;
use TBela\CSS\Parser\SourceLocation;
use TBela\CSS\Parser\ParserTrait;
use TBela\CSS\Parser\Position;
use TBela\CSS\Parser\SyntaxError;
use TBela\CSS\Value\Number;
use function preg_replace_callback;
use function str_replace;
use function substr;

// http://www.w3.org/TR/CSS21/grammar.html
/**
 * @ignore
 */
// const COMMENT_REGEXP = '/\/\*(.*?)\*\//sm';

/**
 * Css Parser
 * @package TBela\CSS
 */
class Parser
{

    use ParserTrait;

    protected Position $currentPosition;
    protected Position $previousPosition;
    protected int $end = 0;


    protected array $errors = [];
    protected array $warnings = [];

    protected $ast = null;

    /**
     * css data
     * @var string
     * @ignore
     */
    public string $css = '';

    /**
     * @var string
     * @ignore
     */
    protected string $path = '';

    /**
     * @var array
     * @ignore
     */
    public array $options = [
        'source' => '',
        'silent' => true,
        'flatten_import' => false,
        'allow_duplicate_rules' => false,
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
     * @return $this
     */

    public function load(string $file): Parser
    {

        $this->path = $file;
        $this->css = $this->getContent($file);
        $this->ast = null;
        return $this;
    }

    /**
     * parse css file and append to the existing AST
     * @param string $file
     * @return $this
     * @throws Exception
     */
    public function append(string $file): Parser
    {

        $parser = (new Parser())->setOptions($this->options)->load($file);
        $parser->parse();

        $this->merge($parser);

        return $this;
    }

    public function merge(Parser $parser): Parser
    {

        if (is_null($this->ast)) {

            $this->doParse();
        }

        if (is_null($parser->ast)) {

            $parser->doParse();
        }

        array_splice($this->ast->elements, count($this->ast->elements), 0, $parser->ast->elements);
        array_splice($this->ast->parsingErrors, count($this->ast->parsingErrors), 0, $parser->ast->parsingErrors);

        $this->ast = $this->deduplicate($this->ast);

        return $this;
    }

    /**
     * set css content
     * @param string $css
     * @return $this
     */
    public function setContent($css)
    {

        $this->css = $css;
        $this->path = '';
        $this->ast = null;
        return $this;
    }

    /**
     * parse css and append to the existing AST
     * @param string $css
     * @return $this
     */
    public function appendContent($css): Parser
    {

        $parser = (new Parser())->setOptions($this->options)->setContent($css);

        $this->merge($parser);
        return $this;
    }

    /**
     * set the parser options
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {

        foreach (array_keys($this->options) as $key) {

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

        return $this;
    }

    /**
     * parse Css
     * @return Element
     * @throws Exception
     */
    public function parse()
    {

        if (is_null($this->ast)) {

            $this->doParse();
        }

        return Element::getInstance($this->ast);
    }

    /**
     * @return stdClass|null
     * @throws SyntaxError
     */
    public function getAst() {

        if (is_null($this->ast)) {

            $this->doParse();
        }

        return $this->ast;
    }

    /**
     *
     * @param $css
     * @param null $path
     * @return string
     * @ignore
     */
    protected function expand($css, $path = null)
    {

        if (!is_null($path) && $path !== '') {

            if ($path[strlen($path) - 1] != '/') {

                $path .= '/';
            }
        }

        $isRemote = preg_match('#^(https?:)//#', $path);

        $css = preg_replace_callback('#url\(([^)]+)\)#', function ($matches) use ($path, $isRemote) {

            $file = trim(str_replace(array("'", '"'), "", $matches[1]));

            if (strpos($file, 'data:') === 0) {

                return $matches[0];
            }

            if ($isRemote) {

                if (!preg_match('#^(https?:)?//#i', $file)) {

                    if ($file[0] == '/') {

                        $file = $path . substr($file, 1);
                    } else {

                        $file = Helper::resolvePath($path . $file);
                    }
                }
            } else if (!preg_match('#^(/|((https?:)?//))#i', $file)) {

                $file = Helper::resolvePath($path . trim(str_replace(array("'", '"'), "", $matches[1])));
            }

            return 'url(' . preg_replace('#^' . preg_quote(Helper::getCurrentDirectory() . '/', '#') . '#', '', $file) . ')';
        },
            //resolve import directive, note import directive in imported css will NOT be processed
            $this->parseImport($css, $path)
        );

        return $css;
    }

    /**
     * @param $file
     * @return string|bool
     * @ignore
     */
    protected function getContent($file)
    {

        if (!preg_match('#^(https?:)//#', $file)) {

            if (is_file($file)) {

                return $this->expand(file_get_contents($file), preg_replace('#^' . preg_quote(Helper::getCurrentDirectory() . '/', '#') . '#', '', dirname($file)));
            }

            return false;
        }

        return $this->expand(Helper::fetchContent($file), dirname($file));
    }

    /**
     * @param $css
     * @param string $path
     * @return string
     * @ignore
     */
    protected function parseImport($css, $path = '')
    {

        $comments = [];
        $css = preg_replace_callback('/\/\*(.*?)\*\//sm', function ($matches) use (&$comments) {

            $comments[$matches[0]] = '~~~b' . md5($matches[0]) . 'b~~~';

            return str_replace($matches[0], $comments[$matches[0]], $matches[0]);
        }, $css);

        $css = preg_replace_callback('#@import ([^;]+);#', function ($matches) use ($path) {

            if (preg_match('#(url\(\s*((["\']?)([^\\3]+)\\3)\s*\)|((["\'])([^\\6]+)\\6))(.*)$#s', $matches[1], $match)) {

                $file = Helper::resolvePath(trim(empty($match[4]) ? $match[7] : $match[4]), $path);

                $media = trim($match[8]);

                if (strpos($media, ' ') !== false) {

                    $media = '(' . $media . ')';
                }

                $css = $this->getContent($file);

                if ($css !== false) {

                    if ($media !== '') {

                        $css = '@media ' . $media . " {\n" . $css . "\n}\n";
                    }

                    return '/* start: @import from ' . $file . ' */' . "\n" . $css . "\n" . '/* end: @import from ' . $file . ' */' . "\n";
                }
            }

            return $matches[0];

        }, $css);

        if (!empty($comments)) {

            $css = str_replace(array_values($comments), array_keys($comments), $css);
        }

        return $css;
    }

    protected function getRoot()
    {

        if (is_null($this->ast)) {

            $this->ast = new stdClass;
            $this->ast->type = 'Stylesheet';
            $this->ast->loc = new SourceLocation(
                new Position(1, 1, 0),
                new Position(1, 1, 0)
            );

            $this->ast->elements = [];
        }
    }

    /**
     * @return stdClass
     * @throws SyntaxError
     */
    protected function doParse()
    {

//        if (is_null($this->ast)) {

        $this->errors = [];
        $this->warnings = [];

        if (!empty($this->options['flatten_import'])) {

            $this->css = $this->parseImport($this->css, $this->path === '' ? Helper::getCurrentDirectory() : dirname($this->path));
        }

        // initialize ast
        $this->getRoot();

        $this->end = strlen($this->css);
        $this->currentPosition = new Position($this->ast->loc->start->line, $this->ast->loc->start->column, 0);
        $this->previousPosition = new Position($this->ast->loc->start->line, $this->ast->loc->start->column, 0);

        while ($this->next()) {

        //    var_dump($this->ast->type.' => '.substr($this->css, $this->currentPosition->index));

            if (substr($this->css, $this->currentPosition->index, 2) == '/*') {

                $comment = static::match_comment($this->css, $this->currentPosition->index, $this->end);

                if ($comment === false) {

                    // unclosed comment
                    $comment = substr($this->css, $this->currentPosition->index) . '*/';
                }

                $this->ast->elements[] = $this->parseComment($comment, clone $this->currentPosition);
                $this->update($this->currentPosition, $comment);

                $this->currentPosition->index += strlen($comment);
                continue;
            }
//
            $substr = static::substr($this->css, $this->currentPosition->index, $this->end - 1, ['{', ';']);

            if (substr($substr, -1) != '{') {

                // parse at-rule

                $node = $this->parseAtRule($substr, clone $this->currentPosition);

                if ($node === false) {

                    $this->warnings[] = new Exception(sprintf('cannot parse token at %s:%s : "%s" %s - %s index %s end %s', $this->previousPosition->line, $this->previousPosition->column,
                        preg_replace('#^(.{40}).*$#sm', '$1... ', $substr), var_export($this->currentPosition), $this->ast->name, $this->currentPosition->index, $this->end));
                    //     continue;
                } else {

                    $this->ast->elements[] = $node;
                }

                $this->update($this->currentPosition, $substr);
                $this->currentPosition->index += strlen($substr);
            } else {

                if (substr($substr, 0, 1) == '@') {

                    $node = $this->parseAtRule($substr, clone $this->currentPosition);

                } else {

                    $node = $this->parseSelector($substr, clone $this->currentPosition);
                }

                $position = $this->update(clone $this->currentPosition, $substr);
                $position->index += strlen($substr);

                if ($node === false) {

                    $block = static::_close($this->css, '}', '{', $position->index, $this->end - 1);
                    $rule = $substr.$block;

                    $this->warnings[] = new Exception(sprintf('cannot parse token at %s:%s. Ignoring rules : "%s"', $this->previousPosition->line, $this->previousPosition->column, preg_replace('#(.{40}).*$#sm', '$1... ', $rule)));
                    $this->update($this->currentPosition, $rule);
                    $this->currentPosition->index += strlen($rule);

                    continue;

                } else {

                    $this->ast->elements[] = $node;

                    $block = static::_close($this->css, '}', '{', $position->index, $this->end - 1);

                    $type = $node->type == 'Rule' ? 'statement' : $this->getBlockType($block);

                    if ($type == 'block') {

                        $parser = (new Parser($block))->setOptions(array_merge($this->options, ['flatten_import' => false]));
                        $parser->path = $this->path;
                        $parser->ast = $node;
                        $node->elements = [];

                        $parser->doParse();

                        if (!empty($parser->warnings)) {

                            array_splice($this->warnings, count($this->warnings), 0, $parser->warnings);
                        }

                    } else {

                        if ($node->type == 'AtRule') {

                            $node->hasDeclarations = true;
                        }

                        $node->elements = $this->parseDeclarations($node, substr($block, 0, -1), $position);
                    }

                    $rule = $substr.$block;
                    $this->update($this->currentPosition, $rule);
                    $this->currentPosition->index += strlen($rule);
                }

                $position = clone $this->currentPosition;
                $position->column--;

                $node->loc->start = clone $this->previousPosition;
                $node->loc->end = $position;
            }
        }

        $this->ast->loc->end->line = $this->currentPosition->line;
        $this->ast->loc->end->index = max(0, $this->currentPosition->index - 1);
        $this->ast->loc->end->column = max($this->currentPosition->column - 1, 1);

        return $this->ast;
    }

    protected function update(Position $position, string $string): Position
    {

        $j = strlen($string);

        for ($i = 0; $i < $j; $i++) {

            if ($string[$i] == "\n") {

                $position->line++;
                $position->column = 1;
            } else {

                $position->column++;
            }
        }

        return $position;
    }

    protected function next()
    {

        $position = $this->getNextPosition($this->css, $this->currentPosition->index, $this->currentPosition->line, $this->currentPosition->column);

        $this->previousPosition->line = $this->currentPosition->line = $position->line;
        $this->previousPosition->column = $this->currentPosition->column = $position->column;
        $this->previousPosition->index = $this->currentPosition->index = $position->index;

        return $this->currentPosition->index < $this->end - 1;
    }

    /**
     * consume whitespace
     * @param string $input
     * @param int $currentIndex
     * @param int $currentLine
     * @param int $currentColumn
     * @return Position
     */
    protected function getNextPosition(string $input, int $currentIndex, int $currentLine, int $currentColumn): Position
    {

        $j = strlen($input);
        $i = $currentIndex;

        if ($currentIndex < 0) {

            echo new Exception('$currentIndex is '.$currentIndex);
        }

        while ($i < $j) {

            if (!in_array($input[$i], [" ", "\t", "\r", "\n"])) {

                break;
            }

            if ($input[$i++] == "\n") {

                $currentLine++;
                $currentColumn = 1;
            } else {

                $currentColumn++;
            }
        }

        return new Position($currentLine, $currentColumn, $i);
    }

    protected function getBlockType(string $block): string
    {

        return substr(static::substr($block, 0, strlen($block) - 1, [';', '{']), -1) == '{' ? 'block' : 'statement';
    }

    protected function parseComment($comment, Position $position)
    {

        $this->update($position, $comment);

        $position->column--;
        $position->index += $this->ast->loc->start->index + strlen($comment);

        return (object)[

            'type' => 'Comment',
            'value' => $comment,
            'loc' => new SourceLocation(
                new Position(
                    $this->currentPosition->line,
                    $this->currentPosition->column,
                    $this->ast->loc->start->index + $this->currentPosition->index),
                $position)
        ];
    }

    protected function getComments(string $string): array
    {

        $result = [];

        if (preg_match_all('/(\/\*.*?\*\/)/sm', $string, $matches)) {

            return $matches[1];
        }

        return $result;
    }

    /**
     * parse @rule like @import, @charset
     * @param string $rule
     * @return false|object
     */
    protected function parseAtRule(string $rule, Position $position)
    {

        if (substr($rule, 0, 1) != '@') {

            return false;
        }

        $comments = $this->getComments($rule);

        if (!empty($comments)) {

            $rule = str_replace(array_map(function ($comment) use ($position) {

                $currentPosition = clone $position;
                $currentPosition->index += $this->ast->loc->start->index;

                $this->update($position, $comment);

                $position->column--;
                $position->index += strlen($comment);

                $this->ast->elements[] = (object)[

                    'type' => 'Comment',
                    'value' => $comment,
                    'loc' => new SourceLocation(
                        $currentPosition,
                        new Position(
                            $position->line,
                            $position->column,
                            $this->ast->loc->start->index + $position->index))
                ];

            }, $comments), '', $rule);

            array_splice($this->ast->elements, count($this->ast->elements), 0, $comments);
        }

        //
        if (!preg_match('/^@((-((moz)|(webkit)|(ms)|o)-)?(\S+))([^;{]+)(.?)/s', $rule, $matches)) {

            return false;
        }

        $currentPosition = clone $position;

        $end = substr($rule, -1);

        $rule = rtrim($rule, ";{ \n\t\r");

        $this->update($position, $rule);

        $node = (object)[

            'type' => 'AtRule',
            'loc' => new SourceLocation($currentPosition, new Position($position->line,
                    $position->column,
                    $this->ast->loc->start->index + $position->index
                )
            ),
            'name' => $matches[7],
            'value' => Value::parse($matches[8])->map(function (Value $value) {

                if ($value instanceof Number) {

                    return Value::getInstance((object)['type' => 'css-string', 'value' => $value->value]);
                }

                return $value;
            })
        ];

        if ($end != '{') {

            $node->isLeaf = true;
        }

        if (!empty($matches[3])) {

            $node->vendor = $matches[3];
        }

        return $node;
    }

    protected function parseSelector(string $rule, Position $position)
    {

        $selectors = [];


        $comments = $this->getComments($rule);

        if (!empty($comments)) {

            $rule = str_replace(array_map(function ($comment) use ($position) {

                $currentPosition = clone $position;
                $currentPosition->index += $this->ast->loc->start->index;

                $this->update($position, $comment);

                $position->column--;
                $position->index += strlen($comment);

                $this->ast->elements[] = (object)[

                    'type' => 'Comment',
                    'value' => $comment,
                    'loc' => new SourceLocation(
                        $currentPosition,
                        new Position(
                            $position->line,
                            $position->column,
                            $this->ast->loc->start->index + $position->index))
                ];

            }, $comments), '', $rule);

            array_splice($this->ast->elements, count($this->ast->elements), 0, $comments);
        }

        $buffer = '';
        $selector = rtrim($rule, "{\n\t\r");

        if (trim($selector) === '') {

            return false;
        }

        $currentPosition = clone $position;
        $this->update($position, $rule);
        $position->column--;
        $position->index += $this->ast->loc->start->index + strlen($rule);

        $i = -1;
        $j = strlen($selector) - 1;

        while (++$i < $j) {

            switch ($selector[$i]) {

                case '\\':

                    $buffer .= $selector[$i];

                    if (isset($selector[$i + 1])) {

                        $buffer .= $selector[++$i];
                    }

                    break;

                case '"':
                case "'":

                    $end = static::_close($selector, $selector[$i], $selector[$i], $i, $j);

                    if ($end === false) {

                        return false;
                    }

                    $buffer .= $end;
                    $i += strlen($end) - 1;

                    break;

                case ',':

                    $buffer = trim($buffer);

                    if ($buffer === '') {

                        return false;
                    }

                    $selectors[] = $buffer;
                    $buffer = '';

                    break;

                case "\n":
                case "\t":
                case "\r":
                case " ":

                    while (++$i < $j && in_array($selector[$i], ["\n", "\t", "\r", " "])) {

                        continue;
                    }

                    if ($i == $j) {

                        break 2;
                    }

                    if ($selector[$i] == ',') {

                        $buffer = trim($buffer);

                        if ($buffer === '') {

                            return false;
                        }

                        $selectors[] = $buffer;
                        $buffer = '';
                    } else {

                        $buffer .= ' ' . $selector[$i];
                    }

                    break;

                default:

                    $buffer .= $selector[$i];
                    break;
            }
        }

        $buffer = trim($buffer);

        if ($buffer !== '') {

            $selectors[] = trim($buffer);
        }

        if (empty($selectors)) {

            return false;
        }

        return (object) [
            'type' => 'Rule',
            'selectors' => array_map(function ($selector) {

                return preg_replace('~([\'"])([\w_-]+)\\1~', '$2', $selector);
            }, $selectors),
            'loc' => new SourceLocation(
                $currentPosition,
                $position),
            'elements' => []
        ];
    }

    protected function parseDeclarations($rule, string $block, Position $position): array
    {

        $declarations = [];

        $j = strlen($block) - 1;
        $i = -1;

        do {

            while (++$i < $j) {

                if (!static::is_whitespace($block[$i]))  {

                    break;
                }

                else {

                    $this->update($position, $block[$i]);
                }
            }

            $statement = static::substr($block, $i, $j, [';', '}']);

            if ($statement === '') {

                break;
            }

            if (in_array(trim($statement), [';', '}'])) {

                $this->update($position, $statement);
                $position->index += strlen($statement);

                $i += strlen($statement);
               continue;
            }

           if (substr($block, $i, 2) == '/*') {

               $comment = static::match_comment($block, $i, $j);

               if ($comment == false) {

                   $comment = substr($block, $i);
               }

               $currentPosition = clone $position;

               $this->update($position, $comment);
               $position->index += strlen($comment);

               $rule->elements[] = (object) [
                   'type' => 'Comment',
                   'value' => $comment,
                   'loc' => new SourceLocation(
                       new Position(
                           $currentPosition->line,
                           $currentPosition->column,
                           $this->ast->loc->start->index + $currentPosition->index
                       ),
                       new Position(
                           $position->line,
                           $position->column - 1,
                           $this->ast->loc->start->index + $position->index
                       )
                   )
               ];

               $i += strlen($comment);
               continue;
           }

           $comments = $this->getComments($statement);

            if (!empty($comments)) {

                $statement = str_replace($comments, '', $statement);

                foreach ($comments as $comment) {

                    $i += strlen($comment);
                    $currentPosition = clone $position;

                    $this->update($position, $comment);
                    $position->index += strlen($comment);

                    $rule->elements[] = (object) [
                        'type' => 'Comment',
                        'value' => $comment,
                        'loc' => new SourceLocation(
                            new Position(
                                $currentPosition->line,
                                $currentPosition->column,
                                $this->ast->loc->start->index + $currentPosition->index
                            ),
                            new Position(
                                $position->line,
                                $position->column - 1,
                                $this->ast->loc->start->index + $position->index
                            )
                        )
                    ];
                }

                array_splice($rule->elements, count($rule->elements), 0, $comments);
            }

            $currentPosition = clone $position;
            $this->update($position, $statement);
            $position->index += strlen($statement);

            $i += strlen($statement);
            $declaration = explode(':', $statement, 2);

            if (count($declaration) != 2) {

                $this->warnings[] = new Exception(sprintf('cannot parse declaration at %s:%s', $currentPosition->line, $currentPosition->column));
            } else {

                $declarations[] = (object)array_merge(
                    [
                        'type' => 'Declaration',
                        'loc' => new SourceLocation(
                            new Position(
                                $currentPosition->line,
                                $currentPosition->column,
                                $currentPosition->index),
                            new Position(
                                $position->line,
                                $position->column - 1,
                                $this->ast->loc->start->index + $position->index
                            )
                        )
                    ],
                    $this->parseVendor(trim($declaration[0])),
                    [
                        'value' => Value::parse(rtrim($declaration[1], ';'), $declaration[0])
                    ]);
            }

        } while ($i < $j);

        return $declarations;
    }

    /**
     * @param $str
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
     * merge css rules and declarations
     * @param Element|object $ast
     * @return object
     */

    public function deduplicate($ast)
    {
        if ($ast instanceof Element) {

            $ast = json_decode(json_encode($ast));
        }

        if ((empty($this->options['allow_duplicate_rules']) || empty($this->options['allow_duplicate_declarations']) || $this->options['allow_duplicate_declarations'] !== true) && !empty ($ast)) {

            switch ($ast->type) {

                case 'AtRule':

                    return !empty($ast->hasDeclarations) ? $this->deduplicateDeclarations($ast) : $this->deduplicateRules($ast);

                case 'Stylesheet':

                    return $this->deduplicateRules($ast);

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
    protected function computeSignature($ast)
    {

        $signature = ['type:' . $ast->type];

        if (isset($ast->name)) {

            $signature[] = 'name:' . $ast->name;
        }

        if (isset($ast->value)) {

            $signature[] = 'value:' . $ast->value;
        }

        if (isset($ast->value)) {

            $signature[] = 'value:' . $ast->value;
        }

        if (isset($ast->selectors)) {

            $signature[] = 'selectors:' . implode(',', $ast->selectors);
        }

        if (!empty($ast->vendor)) {

            $signature[] = 'vendor:' . $ast->vendor;
        }

        return implode(':', $signature);
    }

    /**
     * merge duplicate rules
     * @param object $ast
     * @return object
     * @ignore
     */
    protected function deduplicateRules($ast)
    {

        if (!empty($ast->elements)) {

            if (empty($this->options['allow_duplicate_rules']) && isset($ast->elements)) {

                $signature = '';
                $total = count($ast->elements);
                $el = null;

                while ($total--) {

                    if ($total > 0) {

                        //   $index = $total;
                        $el = $ast->elements[$total];

                        if ($el->type == 'Comment') {

                            continue;
                        }

                        $next = $ast->elements[$total - 1];

                        while ($total > 1 && $next->type == 'Comment') {

                            $next = $ast->elements[--$total - 1];
                        }

                        if ($signature === '') {

                            $signature = $this->computeSignature($el);
                        }

                        $nextSignature = $this->computeSignature($next);

                        while ($signature == $nextSignature) {

                            array_splice($ast->elements, $total - 1, 1);

                            if ($el->type != 'Declaration') {

                                array_splice($el->elements, 0, 0, $next->elements);
                            }

                            if ($total == 1) {

                                break;
                            }

                            $next = $ast->elements[--$total - 1];

                            while ($total > 1 && $next->type == 'Comment') {

                                $next = $ast->elements[--$total - 1];
                            }

                            $nextSignature = $this->computeSignature($next);
                        }

                        $signature = $nextSignature;
                    }
                }
            }

            foreach ($ast->elements as $key => $element) {

                $this->deduplicate($element);
            }
        }

        return $ast;
    }

    /**
     * merge duplicate declarations
     * @param object $ast
     * @return object
     * @ignore
     */
    protected function deduplicateDeclarations($ast)
    {

        if (!empty($this->options['allow_duplicate_declarations']) && !empty($ast->elements)) {

            $elements = $ast->elements;

            $total = count($elements);

            $hash = [];
            $exceptions = is_array($this->options['allow_duplicate_declarations']) ? $this->options['allow_duplicate_declarations'] : !empty($this->options['allow_duplicate_declarations']);

            while ($total--) {

                $declaration = $ast->elements[$total];

                if ($declaration->type == 'Comment') {

                    continue;
                }

                $name = (isset($declaration->vendor) ? '-' . $declaration->vendor . '-' : '') . $declaration->name;

                if ($exceptions === true || isset($exceptions[$name])) {

                    continue;
                }

                if (isset($hash[$name])) {

                    array_splice($ast->elements, $total, 1);
                    continue;
                }

                $hash[$name] = 1;
            }
        }

        return $ast;
    }

    public function getErrors() {

        return $this->warnings;
    }
}
