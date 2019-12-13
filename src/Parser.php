<?php

namespace TBela\CSS;

use \stdClass;

// http://www.w3.org/TR/CSS21/grammar.html
// https://github.com/visionmedia/css-parse/pull/49#issuecomment-30088027
const COMMENT_REGEXP = '/\/\*(.*?)\*\//sm';

class Parser
{

    protected $imports = [];
    public $css = '';
    public $options = [];
    public $errorsList = [];

    public function __construct($css, $options = [])
    {

        $this->_css = $css;
        $this->options = $options;

        if (!isset($this->options['source'])) {

            $this->options['source'] = '';
        }

        if (!isset($this->options['silent'])) {

            $this->options['silent'] = false;
        }
    }

    public function parse() {

        $this->css = $this->_css;

        if (!empty($this->options['flatten_import'])) {

            $this->css = parse_import($this->css);
        }

        return parse($this);
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
        //	$name = GZipHelper::getName($file);
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
        parse_import($css)
    );

    return $css;
}

function resolvePath($path)
{

    if (strpos($path, '../') !== false) {

        $return = [];

        if (strpos($path, '/') === 0)
            $return[] = '/';

        foreach (explode('/', $path) as $p) {

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

    return $path;
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

        //    error_log('curl error :: ' . $url . ' #' . curl_errno($ch) . ' :: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    return $result;
}

function parse_import($css)
{

    $comments = [];

    $css = \preg_replace_callback(COMMENT_REGEXP, function ($matches) use (&$comments) {

        $comments[$matches[0]] = '~~~b' . md5($matches[0]) . 'b~~~';

        return \str_replace($matches[0], $comments[$matches[0]], $matches[0]);
    }, $css);

    $css = preg_replace_callback('#@import ([^;]+);#', function ($matches) {

        if (preg_match('#(url\(\s*((["\'])([^\\3]+)\\3)\s*\)|((["\'])([^\\6]+)\\6))(.*)$#s', $matches[1], $match)) {

            $file = empty($match[4]) ? $match[7] : $match[4];

            $media = trim($match[8]);

            if (strpos($media, ' ') !== false) {

                $media = '(' . $media . ')';
            }

            $css = get_content($file);

            if ($css !== false) {

                if ($media !== '') {

                    $css = '@media ' . $media . " {\n" . $css . "\n}";
                }

                return '/* @imported ' . resolvePath($file) . ' */' . "\n" . $css;
            }
        }

        return $matches[0];

    }, $css);

    if (!empty($comments)) {

        $css = \str_replace(array_values($comments), array_keys($comments), $css);
    }

    return $css;
}

function parse($context)
{

    /**
     * Positional.
     */

    $context->lineno = 1;
    $context->column = 1;

    /**
     * Update lineno and column based on `str`.
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
     * Mark position and patch `node.position`.
     */

    /*
   function position($context) {
     $start = new stdClass;
     $start->line = $context->lineno;
     $start->column = $context->column;

     return function($node) use($start, $context) {

       whitespace($context);
       return $node;
     };
   }
   */

    /**
     * Error `msg`.
     */

    function error($msg, $context)
    {
        $err = new stdClass;

        // grab the first line?
        $source = \substr($context->css, 0, 80);
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
            throw new \Exception($err->message);
        }
    }

    /**
     * Parse stylesheet.
     */

    function stylesheet($context)
    {

        $rulesList = rules($context);

        $result = new stdClass;

        $result->type = 'stylesheet';
        //  $result->stylesheet = new stdClass;
        //  $result->stylesheet->source = $context->options['source'];
        $result->elements = $rulesList;
        $result->parsingErrors = $context->errorsList;

    //    var_dump($result);

        return $result;
    }

    /**
     * Opening brace.
     */

    function open($context)
    {
        return match('/^{\s*/s', $context);
    }

    /**
     * Closing brace.
     */

    function close($context)
    {
        return match('/^}/', $context);
    }

    function debug($str, $return = false)
    {

        $str = \explode("\n", substr($str, 0, 80), 3);

        unset($str[2]);


        $e = new \Exception();
        $trace = explode("\n", $e->getTraceAsString());

        // array_shift($trace);

        $result = implode("\n", $str) . "\n" . implode("\n", $trace) . "\n";

        if ($return) {

            return $result;
        }

        echo $result;
    }

    function parse_vendor($str)
    {

        if (preg_match('/^((-((moz)|(webkit)|(ms)|o)-)?(\S+))/', trim($str), $match)) {

            return [

                'name' => $match[7],
                'vendor' => $match[3]
            ];
        }

        return ['value' => $str];
    }

    /**
     * Parse ruleset.
     */

    function rules($context)
    {

        //  $node;
        $rules = [];

        while ($context->css !== '') {

            //   comments($rules, $context);
            whitespace($context);

            if ($context->css !== '') {

                //   comments($rules, $context);
                //   whitespace($context);

                if (preg_match('#^/\*#', $context->css)) {

                    comments($rules, $context);
                } else if (preg_match('/^@((-((moz)|(webkit)|(ms)|o)-)?(\S+))([^;{]+)/s', $context->css)) {

                    $node = atanyrule($context);

                    if ($node instanceof \Exception) throw $node;

                    if ($node !== false) {

                        $rules[] = $node;
                    }
                } else {

                    $node = rule($context);

                    if ($node instanceof \Exception) throw $node;

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
     */

    function match($re, $context)
    {

        preg_match($re, $context->css, $m);

        if (!$m) return;
        $str = $m[0];
        updatePosition($str, $context);

        $context->css = substr($context->css, strlen($str));
        return $m;
    }

    /**
     * Parse whitespace.
     */

    function whitespace($context)
    {
        match('/^\s*/s', $context);
    }

    /**
     * Parse comments;
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
     */

    function comment($context)
    {
        if ($context->css === '' || '/' != $context->css[0] || '*' != $context->css[1]) return;

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
     */

    function selector($context)
    {

        //  if ($context->css !== '' && $context->css[0] == '@') return;

        $m = match('/^[^@]([^{]+)/s', $context);

        if (!$m) return;
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

        },
            preg_replace(COMMENT_REGEXP, '', $m[0]))));
    }

    /**
     * Parse declaration.
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
     * Parse declarations.
     */

    function declarations($context)
    {

        $decls = [];

        if (!open($context)) return error("missing '{'", $context);

        comments($decls, $context);

        // declarations
        //   $decl;
        while ($decl = declaration($context)) {
            if ($decl !== false) {
                $decls[] = $decl;
                comments($decls, $context);
            }
        }

        if (!close($context)) return error("missing '}'", $context);
        return $decls;
    }

    /**
     * Parse keyframe.
     */

    function keyframe($context)
    {
        //  $m;
        $vals = [];
        whitespace($context);

        while ($m = match('/^((\d+\.\d+|\.\d+|\d+)%?|[a-z]+)\s*/s', $context)) {
            $vals[] = $m[1];
            match('/^,\s*/s', $context);
        }

        if (!count($vals)) return;

        $data = [

            'type' => 'keyframe',
            'values' => $vals,
            'declarations' => declarations($context)
        ];

        settype($data, 'object');

        return $data;
    }

    /**
     * Parse keyframes.
     */

    function atkeyframes($context)
    {
        whitespace($context);
        $m = match('/^@([-\w]+)?keyframes\s*/s', $context);

        if (!$m) return;

        $vendor = isset($m[1]) ? $m[1] : '';

        // identifier
        $m = match('/^([-\w]+)\s*/s', $context);
        if (!$m) return error("@keyframes missing name", $context);
        $name = $m[1];

        if (!open($context)) return error("@keyframes missing '{'", $context);

        //  $frame;
        $frames = [];
        comments($frames, $context);
        while ($frame = keyframe($context)) {
            $frames[] = $frame;
            comments($frame, $context);
        }

        if (!close($context)) return error("@keyframes missing '}'", $context);

        $data = [
            'type' => 'keyframes',
            'name' => $name,
            'vendor' => $vendor,
            'keyframes' => $frames
        ];

        settype($data, 'object');

        return $data;
    }

    /**
     * Parse any atrule.
     */

    function atanyrule($context)
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

            //   var_dump($data);

            settype($data, 'object');

            return $data;
        }

        if ($context->css[0] == '{') {

            //  match('/^([^}]+)\{/s', $context);

            $elements = [];
            //  comments($elements, $context);

            if (!open($context)) return error("@$m[1] missing '{'", $context);

            $data = [

                'type' => 'atrule',
                'name' => $m[7],
                'value' => trim($m[8])
            ];

            /*
            if (in_array($m[7], ['viewport', 'font-face', 'page', 'counter-style'])) {

                comments($elements, $context);

                while ($res = declaration($context)) {

                    $elements[] = $res;
                    comments($elements, $context);
                }

                $data['hasDeclarations'] = true;

            }

            else
                if ($m[7] == 'keyframes') {

                //  debug($m[7].' '.$context->css);

                comments($elements, $context);

                while ($res = rule($context)) {

                    $elements[] = $res;
                    comments($elements, $context);
                }
                    */
        //    } else {

                //  echo __LINE__.' '.$m[7].' -> '. debug($context->css, true);

                while ($context->css !== '' && $context->css[0] != '}') {
                   
                //    whitespace($context);
                    comments($elements, $context);

                    if ($context->css !== '') {

                    //    var_dump($context->css[0]);
                        if ($context->css[0] == '@') {

                            $res = atanyrule($context);
                        }

                        else if (preg_match('#([^;}{]+)([;}{])#s', $context->css, $matches)) {

                            //    if (trim($matches[1]) !== '' && $matches[2] == ';') {

                                //    $data['hasDeclarations'] = true;
                            //    }

                             //   var_dump(['$context->css[0]' => $context->css[0], '$matches[2]' => $matches[2]]);

                                if ($matches[2] == '{') {

                                    $res = rule($context);
                                }
                                else {

                                    while ($res = declaration ($context)) {

                                        $elements[] = $res;
                                        comments($elements, $context);
                                    }

                                    continue;
                                }

                                /*
                                if ($context->css[0] == '{') {

                                    while ($res = declarations ($context)) {

                                        $element = array_merge($elements, $res);
                                        whitespace($context);
                                        comments($elements, $context);
                                    }
                                }

                                else {

                                    while ($res = declaration ($context)) {

                                        $elements[] = $res;
                                        whitespace($context);
                                        comments($elements, $context);
                                    }
                                }

                            //    continue;
                                */
                            }

                        //    else {

                             //   $res = rule($context);
                        //    }
                        //    debug($context->css);

                        //    var_dump(['$s' => $s]);

                    //    }

                        if ($res) {
                                
                            $elements[] = $res;
                        }

                        comments($elements, $context);
                        whitespace($context);
                    }
                }
       //     }

            if (!close($context)) return error("@$m[1] missing '}'", $context);

            $data['elements'] = $elements;

            if (!empty($m[2])) {

                $data['vendor'] = $m[3];
            }

            settype($data, 'object');

            return $data;
        }

        //  var_dump($context->css[0], $m[1], $m[3], $m[8]);die;

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

                    $node = atanyrule($context);

                    if ($node !== false) {

                        $c = array_merge($c, $node);
                        continue;
                    }
                }
            }

            $res = declaration($context);

            if ($res !== false && !($res instanceof \Exception)) {

                $c[] = $res;
            }
        }

        // var_dump(['iscolde' => debug($context->css, true)]);

        // var_dump(['$res' => $res]);

//  debug($context->css);

        if (!close($context)) return error(implode(', ', $sel) . " missing '}'", $context);

        $data = [

            'type' => 'rule',
            'selectors' => $sel,
            'elements' => $c
        ];

        settype($data, 'object');

        return $data;
    }

    return stylesheet($context);
}

;
