<?php

namespace TBela\CSS;

use Exception;
use \stdClass;
use function preg_replace_callback;
use function str_replace;
use function substr;

// http://www.w3.org/TR/CSS21/grammar.html
const COMMENT_REGEXP = '/\/\*(.*?)\*\//sm';

class Parser
{

    public $css = '';
    protected $_css = '';
    protected $path = '';

    protected $options = [
        'source' => '',
        'silent' => false,
        'flatten_import' => false,
        'deduplicate_rules' => true,
        'deduplicate_declarations' => true
    ];

    public $errorsList = [];

    /**
     * Parser constructor.
     * @param string $css
     * @param array $options
     */
    public function __construct($css = '', array $options = [])
    {
        $this->setContent($css);
        $this->setOptions($options);
    }

    /**
     * @param string $file
     * @return $this
     */
    public function load($file)
    {

        $this->path = $file;
        $this->_css = file_get_contents($file);
        return $this;
    }

    /**
     * @param string $css
     * @return $this
     */
    public function setContent($css)
    {

        $this->_css = $css;
        $this->path = '';
        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {

        foreach (array_keys($this->options) as $key) {

            if (isset($options[$key])) {

                $this->options[$key] = $options[$key];
            }
        }

        return $this;
    }

    /**
     * @return object
     * @throws Exception
     */
    public function parse()
    {
        $this->css = $this->_css;
        $this->errorsList = [];

        if (!empty($this->options['flatten_import'])) {

            $this->css = parse_import($this->css, dirname($this->path));
        }

        return $this->deduplicate(parse($this));
    }

    /**
     * @param object $ast
     * @return object
     */
    protected function deduplicate($ast)
    {

        if ((!empty($this->options['deduplicate_rules']) || !empty($this->options['deduplicate_declarations'])) && !empty ($ast)) {

            switch ($ast->type) {

                case 'atrule':

                    return !empty($ast->hasDeclarations) ? $this->deduplicateDeclarations($ast) : $this->deduplicateRules($ast);

                case 'stylesheet':

                    return $this->deduplicateRules($ast);

                case 'rule':

                    return $this->deduplicateDeclarations($ast);
            }
        }

        return $ast;
    }

    /**
     * @param object $ast
     * @return string
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
     * @param object $ast
     * @return object
     */
    protected function deduplicateRules($ast)
    {

        if (!empty($ast->elements)) {

            if (!empty($this->options['deduplicate_rules']) && isset($ast->elements)) {

                $signature = '';
                $total = count($ast->elements);
                $el = null;

                while ($total--) {

                    if ($total > 0) {

                        $el = $ast->elements[$total];
                        $next = $ast->elements[$total - 1];

                        if ($el->type == 'comment') {

                            continue;
                        }

                        while ($total > 1 && $next->type == 'comment') {

                            $next = $ast->elements[--$total - 1];
                        }

                        if ($signature === '') {

                            $signature = $this->computeSignature($el);
                        }

                        $nextSignature = $this->computeSignature($next);

                        if ($signature == $nextSignature) {

                                array_splice($ast->elements, $total - 1, 1);
                                array_splice($el->elements, 0, 0, $next->elements);
                        }

                        $signature = $nextSignature;
                    }
                }
            }

            foreach ($ast->elements as $element) {

                $this->deduplicate($element);
            }
        }

        return $ast;
    }

    /**
     * @param object $ast
     * @return object
     */
    protected function deduplicateDeclarations($ast)
    {

        if (!empty($this->options['deduplicate_declarations']) && !empty($ast->elements)) {

            $elements = $ast->elements;

            $total = count($elements);

            $hash = [];

            while ($total--) {

                $declaration = $ast->elements[$total];

                if ($declaration->type == 'comment') {

                    continue;
                }

                $name = (isset($declaration->vendor) ? '-' . $declaration->vendor . '-' : '') . $declaration->name;

                if (isset($hash[$name])) {

                    array_splice($ast->elements, $total, 1);
                    continue;
                }

                $hash[$name] = 1;
            }
        }

        return $ast;
    }
}

function get_content($file)
{

    if (!preg_match('#^(https?:)//#', $file)) {

        if (is_file($file)) {

            return expand(file_get_contents($file), dirname($file));
        }

        return false;
    }

    return expand(fetch_content($file), dirname($file));
}

function expand($css, $path = null)
{

    if (!is_null($path)) {

        if (!preg_match('#/$#', $path)) {

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

                    $file = resolvePath($path . $file);
                }
            }
        } else if (!preg_match('#^(/|((https?:)?//))#i', $file)) {

            $file = resolvePath($path . trim(str_replace(array("'", '"'), "", $matches[1])));
        }

        return 'url(' . $file . ')';
    },
        //resolve import directive, note import directive in imported css will NOT be processed
        parse_import($css, $path)
    );

    return $css;
}

function resolvePath($file, $path = '')
{

    if ($path !== '') {

        if (!preg_match('#^(https?:/)?/#', $file)) {

            $file = $path.'/'.$file;
        }
    }

    if (strpos($file, '../') !== false) {

        $return = [];

        if (strpos($file, '/') === 0)
            $return[] = '/';

        foreach (explode('/', $file) as $p) {

            if ($p == '..') {

                array_pop($return);
                continue;

            } else if ($p == '.') {

                continue;

            } else {

                $return[] = $p;
            }
        }

        return implode('/', $return);
    }

    return $file;
}

function fetch_content($url, $options = [], $curlOptions = [])
{

    if (strpos($url, '//') === 0) {

        $url = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http' : 'https') . ':' . $url;
    }

    $ch = curl_init($url);

    if (strpos($url, 'https://') === 0) {

        // Turn on SSL certificate verfication
        curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    }

    if (!empty($curlOptions)) {

        curl_setopt_array($ch, $curlOptions);
    }

    if (!empty($options)) {

        // Tell the curl instance to talk to the server using HTTP POST
        curl_setopt($ch, CURLOPT_POST, count($options));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($options));
    }

    // 1 second for a connection timeout with curl
    //    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    // Try using this instead of the php set_time_limit function call
    //    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
    // Causes curl to return the result on success which should help us avoid using the writeback option
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($ch);

    //    if(curl_errno($ch)) {
    //    }

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {

        curl_close($ch);
        return false;
    }

    curl_close($ch);

    return $result;
}

function parse_import($css, $path = '')
{

    $comments = [];

    $css = preg_replace_callback(COMMENT_REGEXP, function ($matches) use (&$comments) {

        $comments[$matches[0]] = '~~~b' . md5($matches[0]) . 'b~~~';

        return str_replace($matches[0], $comments[$matches[0]], $matches[0]);
    }, $css);

    $css = preg_replace_callback('#@import ([^;]+);#', function ($matches) use ($path) {

        if (preg_match('#(url\(\s*((["\'])([^\\3]+)\\3)\s*\)|((["\'])([^\\6]+)\\6))(.*)$#s', $matches[1], $match)) {

            $file = resolvePath(empty($match[4]) ? $match[7] : $match[4], $path);

            $media = trim($match[8]);

            if (strpos($media, ' ') !== false) {

                $media = '(' . $media . ')';
            }

            $css = get_content($file);

            if ($css !== false) {

                if ($media !== '') {

                    $css = '@media ' . $media . " {\n" . $css . "\n}";
                }

                return '/* start: @import from ' . $file . ' */' . "\n" . $css . "\n" . '/* end: @import from ' . $file . ' */'. "\n" ;
            }
        }

        return $matches[0];

    }, $css);

    if (!empty($comments)) {

        $css = str_replace(array_values($comments), array_keys($comments), $css);
    }

    return $css;
}

/**
 * Update lineno and column based on `str`.
 * @param string $str
 * @param object $context
 */

function updatePosition($str, $context)
{

    preg_match('/\n/s', $str, $lines);

    if ($lines) $context->lineno += count($lines);

    $i = strrpos($str, '\n');

    if ($i === false) {

        $i = -1;
    }

    $length = strlen($str);

    $context->column = ~$i ? $length - $i : $context->column + $length;
}

/**
 * Error `msg`.
 * @param string $msg
 * @param object $context
 * @return Exception
 * @throws Exception
 */

function error($msg, $context)
{
    $err = new stdClass;

    // grab the first line?
    $source = substr($context->css, 0, 80);
    $source = explode("\n", $source, 3);

    unset($source[2]);

    $source = implode("\n", $source);

    $err->message = $context->options['source'] . ':' . $context->lineno . ':' . $context->column . ': ' . $msg
        . ("\n" . $source . ' ...' . "\n");

    $err->reason = $msg;
    $err->filename = $context->options['source'];
    $err->line = $context->lineno;
    $err->column = $context->column;
    $err->source = $context->css;

    if ($context->options['silent']) {
        $context->errorsList[] = $err;
    } else {
        throw new Exception($err->message);
    }

    return new Exception($err->message);
}

/**
 * Parse stylesheet.
 * @param $context
 * @return stdClass
 * @throws Exception
 */

function stylesheet($context)
{

    $rulesList = rules($context);

    $result = new stdClass;

    $result->type = 'stylesheet';
    $result->elements = $rulesList;
    $result->parsingErrors = $context->errorsList;

    return $result;
}

/**
 * Opening brace.
 * @param $context
 * @return string
 */

function open($context)
{
    return match('/^{\s*/s', $context);
}

/**
 * Closing brace.
 * @param object $context
 * @return string
 */

function close($context)
{
    return match('/^}/', $context);
}

function parse_vendor($str)
{

    if (preg_match('/^((-((moz)|(webkit)|(ms)|o)-)(\S+))/', trim($str), $match)) {

        return [

            'name' => $match[7],
            'vendor' => $match[3]
        ];
    }

    return ['name' => $str];
}

/**
 * Parse ruleset.
 * @param object $context
 * @return array
 * @throws Exception
 */

function rules($context)
{

    //  $node;
    $rules = [];

    while ($context->css !== '') {

        //   comments($rules, $context);
        whitespace($context);

        if ($context->css !== '') {

            if (preg_match('#^/\*#', $context->css)) {

                comments($rules, $context);
            } else if (preg_match('/^@((-((moz)|(webkit)|(ms)|o)-)?(\S+))([^;{]+)/s', $context->css)) {

                $node = atrule($context);

                if ($node instanceof Exception) throw $node;

                if ($node !== false) {

                    $rules[] = $node;
                }
            } else {

                $node = rule($context);

                if ($node instanceof Exception) throw $node;

                if ($node !== false) {

                    $rules[] = $node;
                }
            }
        }
    }
    return $rules;
}

/**
 * Match `re` and return captures.
 *
 * @param string $re
 * @param object $context
 * @return string
 */

function match($re, $context)
{

    preg_match($re, $context->css, $m);

    if (!$m) return '';
    $str = $m[0];
    updatePosition($str, $context);

    $context->css = substr($context->css, strlen($str));
    return $m;
}

/**
 * Parse whitespace.
 * @param object $context
 */

function whitespace($context)
{
    match('/^\s*/s', $context);
}

/**
 * Parse comments;
 * @param array $rules
 * @param object $context
 * @return array
 * @throws Exception
 */

function comments(&$rules, $context)
{

    //  if (empty($rules)) $rules = [];
    while ($c = comment($context)) {
        if ($c !== false) {
            $rules[] = $c;
        }

        whitespace($context);
    }
    return $rules;
}

/**
 * Parse comment.
 * @param object $context
 * @return Exception|stdClass|bool
 * @throws Exception
 */

function comment($context)
{
    if ($context->css === '' || '/' != $context->css[0] || '*' != $context->css[1]) return false;

    if (!preg_match(COMMENT_REGEXP, $context->css, $m)) {

        return error('End of comment missing', $context);
    }

    $str = $m[0];

    $i = strlen($str);
    updatePosition($str, $context);
    $context->css = substr($context->css, $i);

    $data = new stdClass;

    $data->type = 'comment';
    $data->value = $m[0];

    return $data;
}

/**
 * Parse selector.
 * @param object $context
 * @return array
 */

function selector($context)
{

    $m = match('/^[^@]([^{]+)/s', $context);

    if (!$m) return [];
    /* @fix Remove all comments from selectors
     * http://ostermiller.org/findcomment.html */

    $map = [];

    return array_map(function ($token) use (&$map) {

        return trim(str_replace(array_values($map), array_keys($map), $token));

    }, explode(',', preg_replace_callback([

        //	'/"(?:\\"|[^"])*"|\'(?:\\\'|[^\'])*\'/s',
        '/(\([^)]*?\))/s',
        '/(\[[^\]]*?\])/s'
    ], function ($matches) use (&$map) {

        if (!isset($matches[1])) {

            return $matches[0];
        }

        $map[$matches[1]] = '~~~' . md5($matches[1]) . '~~~';

        return str_replace($matches[1], $map[$matches[1]], $matches[0]);

    }, preg_replace(COMMENT_REGEXP, '', $m[0]))));
}

/**
 * Parse declaration.
 * @param object $context
 * @return array|bool|Exception
 * @throws Exception
 */

function declaration($context)
{
    whitespace($context);

    if ($context->css !== '' && $context->css[0] == '}') return false;

    // prop
    $prop = match('/^(\*?[-#\/\*\w]+(\[[0-9a-z_-]+\])?)\s*/si', $context);

    if (!$prop) return false;
    $prop = trim($prop[0]);

    // :
    if (!match('/^:\s*/', $context)) return error("property missing ':'", $context);

    // val
    $val = match('/^((?:\'(?:\\\'|.)*?\'|"(?:\\"|.)*?"|\([^\)]*?\)|[^};])+)/s', $context);

    $data = [
        'type' => 'declaration',
        'name' => preg_replace(COMMENT_REGEXP, '', $prop),
        'value' => $val ? preg_replace(COMMENT_REGEXP, '', trim($val[0])) : ''
    ];

    foreach (parse_vendor($data['name']) as $key => $value) {

        $data[$key] = $value;
    }

    settype($data, 'object');
    match('/^[;\s]*/', $context);

    return $data;
}

/**
 * Parse any atrule.
 * @param $context
 * @return array|bool|Exception|void
 * @throws Exception
 */
function atrule($context)
{

    $m = match('/^@((-((moz)|(webkit)|(ms)|o)-)?(\S+))([^;{]+)/s', $context);

    if (!$m) return false;

    if ($context->css[0] == ';') {

        match('/^;+/s', $context);

        $data = [

            'type' => 'atrule',
            'name' => $m[7],
            'isLeaf' => true,
            'value' => trim($m[8])
        ];

        if (!empty($m[2])) {

            $data['vendor'] = $m[3];
        }

        settype($data, 'object');

        return $data;
    }

    if ($context->css[0] == '{') {

        $elements = [];

        if (!open($context)) return error("@$m[1] missing '{'", $context);

        $data = [

            'type' => 'atrule',
            'name' => $m[7],
            'value' => trim($m[8])
        ];

        while ($context->css !== '' && $context->css[0] != '}') {

            comments($elements, $context);

            if ($context->css !== '') {

                if ($context->css[0] == '@') {

                    $res = atrule($context);
                } else if (preg_match('#([^;}{]+)([;}{])#s', $context->css, $matches)) {

                    if ($matches[2] == '{') {

                        $res = rule($context);
                    } else {

                        while ($res = declaration($context)) {

                            $elements[] = $res;
                            comments($elements, $context);
                        }

                        $data['hasDeclarations'] = true;

                        continue;
                    }
                }

                if (!empty($res)) {

                    $elements[] = $res;
                }

                comments($elements, $context);
                whitespace($context);
            }
        }

        if (!close($context)) return error("@$m[1] missing '}'", $context);

        $data['elements'] = $elements;

        if (!empty($m[2])) {

            $data['vendor'] = $m[3];
        }

        settype($data, 'object');

        return $data;
    }

    $style = [];
    $style = array_merge(comments($style, $context), rules($context));

    if (!close($context)) return error("@host missing '}'", $context);

    $data = [

        'type' => 'host',
        'rules' => $style
    ];

    settype($data, 'object');

    return pos($data);
}

/**
 * Parse rule.
 * @param object $context
 * @return array|bool|Exception
 * @throws Exception
 */

function rule($context)
{

    $c = [];
    whitespace($context);

    comments($c, $context);

    if ($context->css === '' || $context->css[0] == '}') return empty($c) ? false : $c;

    $sel = selector($context);

    if (!$sel) return error('selector missing', $context);

    comments($c, $context);
    whitespace($context);

    if (!open($context)) return error(implode(', ', $sel) . " missing '{'", $context);

    whitespace($context);
    comments($c, $context);

    while ($context->css !== '' && $context->css[0] != '}') {

        comments($c, $context);
        whitespace($context);

        if ($context->css !== '') {

            if (preg_match('/^@((-((moz)|(webkit)|(ms)|o)-)?(\S+))([^;{]+)/s', $context->css)) {

                $node = atrule($context);

                if ($node !== false) {

                    $c = array_merge($c, $node);
                    continue;
                }
            }
        }

        $res = declaration($context);

        if ($res !== false && !($res instanceof Exception)) {

            $c[] = $res;
        }
    }

    if (!close($context)) return error(implode(', ', $sel) . " missing '}'", $context);

    $data = [

        'type' => 'rule',
        'selectors' => $sel,
        'elements' => $c
    ];

    settype($data, 'object');

    return $data;
}

/**
 * @param object $context
 * @return stdClass
 * @throws Exception
 */
function parse($context)
{

    /**
     * Positional.
     */

    $context->lineno = 1;
    $context->column = 1;

    return stylesheet($context);
}
