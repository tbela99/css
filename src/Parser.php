<?php 

namespace CSS;
use \stdClass;

// http://www.w3.org/TR/CSS21/grammar.html
// https://github.com/visionmedia/css-parse/pull/49#issuecomment-30088027
const COMMENT_REGEXP = '/\/\*(.*?)\*\//sm';

class Parser {

	protected $imports = [];
	public $css = '';
	public $options = [];
	public $errorsList = [];

	public function __construct($css, $options = []) {

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

function parse_import($css) {
			
	return preg_replace_callback('#@import ([^;]+);#', function ($matches) {

		if (preg_match('#(url\(\s*((["\'])([^\\3]+)\\3)\s*\)|((["\'])([^\\6]+)\\6))(.*)$#s', $matches[1], $match)) {

			$file = empty($match[4]) ? $match[7] : $match[4];

			$media = trim($match[8]);

			if (strpos($media, ' ') !== false) {

				$media = '('.$media.')';
			}

			$css = get_content($file);

			if ($css !== false) {

				if ($media !== '') {

					$css = '@media '.$media." {\n".$css."\n}";
				}

				return '/* @imported '.resolvePath($file).' */'."\n". $css;
			}
		}

		return $matches[0];

	}, $css);
}

function get_content($file) {

	if (!preg_match('#^(https?:)//#', $file)) {

		if (is_file ($file)) {

			return expand(file_get_contents($file), dirname($file));
		}

		return false;
	}

	return expand(fetch_content($file), dirname($file));
}

function expand($css, $path = null) {

	if (!is_null($path)) {

		if (!preg_match('#/$#', $path)) {

			$path .= '/';
		}
	}

	$isRemote = preg_match('#^(https?:)//#', $path);

	$css = preg_replace_callback('#url\(([^)]+)\)#', function ($matches) use($path, $isRemote) {

		$file = trim(str_replace(array("'", '"'), "", $matches[1]));

		if (strpos($file, 'data:') === 0) {

			return $matches[0];
		}
	//	$name = GZipHelper::getName($file);
		if ($isRemote) {

			if (!preg_match('#^(https?:)?//#i', $file)) {
					
				if ($file[0] == '/') {

					$file = $path.substr($file, 1);
				}

				else {

					$file = resolvePath($path.$file);
				}
			}
		}

		else if (!preg_match('#^(/|((https?:)?//))#i', $file)) {

			$file = resolvePath($path . trim(str_replace(array("'", '"'), "", $matches[1])));
		}

		return 'url(' . $file.')';
	},
	//resolve import directive, note import directive in imported css will NOT be processed
	parse_import($css)
);

	return $css;
}

function resolvePath($path) {

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

function fetch_content($url, $options = [], $curlOptions = []) {

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

function parse($context){

  /**
   * Positional.
   */

   $context->lineno = 1;
   $context->column = 1;

  /**
   * Update lineno and column based on `str`.
   */

  function updatePosition($str, $context) {

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

  function position($context) {
    $start = new stdClass;
    $start->line = $context->lineno;
    $start->column = $context->column;

    return function($node) use($start, $context) {
      
      whitespace($context);
      return $node;
    };
  }

  /**
   * Error `msg`.
   */

  function error($msg, $context) {
    $err = new stdClass;
    
    $err->message = $context->options['source'] . ':'. $context->lineno. ':'. $context->column . ': ' .$msg;
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

  function stylesheet($context) {
	$rulesList = rules($context);
	
    $result = new stdClass;

    $result->type = 'stylesheet';
    $result->stylesheet = new stdClass;
    $result->stylesheet->source = $context->options['source'];
    $result->stylesheet->rules = $rulesList;
    $result->parsingErrors = $context->errorsList;

    return $result;
  }

  /**
   * Opening brace.
   */

  function open($context) {
    return match('/^{\s*/s', $context);
  }

  /**
   * Closing brace.
   */

  function close($context) {
    return match('/^}/s', $context);
  }

  /**
   * Parse ruleset.
   */

  function rules($context) {
  //  $node;
    $rules = [];
	whitespace($context);
	
	
	comments($rules, $context);

    $length = strlen($context->css);
    
    while ($context->css !== '' && $length && $context->css[0] != '}' && (($node = atrule($context)) || ($node = rule($context)))) {
      if ($node !== false) {
        $rules[] = $node;
        comments($rules, $context);
      }
    }
    return $rules;
  }

  /**
   * Match `re` and return captures.
   */

  function match($re, $context) {

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

  function whitespace($context) {
    match('/^\s*/s', $context);
  }

  /**
   * Parse comments;
   */

  function comments(&$rules, $context) {
  //  $c;
    if (empty($rules)) $rules = [];
    while ($c = comment($context)) {
      if ($c !== false) {
        $rules[] = $c;
      }
    }
    return $rules;
  }

  /**
   * Parse comment.
   */

  function comment($context) {
    $pos = position($context);
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
    $data->comment = $m[0];

    return $pos($data);
  }

  /**
   * Parse selector.
   */

  function selector($context) {
    $m = match('/^([^{]+)/s', $context);

    if (!$m) return;
    /* @fix Remove all comments from selectors
	 * http://ostermiller.org/findcomment.html */
	 		
	$map = [];

	return array_map(function ($token) use(&$map) {

			return trim(str_replace(array_values($map), array_keys($map), $token));
		
		}, explode(',', preg_replace_callback([
			
	//	'/"(?:\\"|[^"])*"|\'(?:\\\'|[^\'])*\'/s',
		'/(\([^)]*?\))/s',
		'/(\[[^\]]*?\])/s'
	], function ($matches) use(&$map) {

			if(!isset($matches[1])) {

				return $matches[0];
			}

			$map[$matches[1]] = '~~~'.md5($matches[1]).'~~~';

			return str_replace($matches[1], $map[$matches[1]], $matches[0]);

	}, 
	preg_replace(COMMENT_REGEXP, '', $m[0]))));
  }

  /**
   * Parse declaration.
   */

  function declaration($context) {
    $pos = position($context);

    // prop
    $prop = match('/^(\*?[-#\/\*\w]+(\[[0-9a-z_-]+\])?)\s*/si', $context);

	if (!$prop) return;
    $prop = trim($prop[0]);

    // :
    if (!match('/^:\s*/', $context)) return error("property missing ':'", $context);

    // val
	$val = match('/^((?:\'(?:\\\'|.)*?\'|"(?:\\"|.)*?"|\([^\)]*?\)|[^};])+)/s', $context);

	$data = [
		'type' => 'declaration', 
		'property' => preg_replace(COMMENT_REGEXP, '', $prop),
		'value' => $val ? preg_replace(COMMENT_REGEXP, '', trim($val[0])) : ''
	];

	settype($data, 'object');

    $ret = $pos($data);

    // ;
    match('/^[;\s]*/', $context);

    return $ret;
  }

  /**
   * Parse declarations.
   */

  function declarations($context) {
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

  function keyframe($context) {
  //  $m;
    $vals = [];
    $pos = position($context);

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

    return $pos($data);
  }

  /**
   * Parse keyframes.
   */

  function atkeyframes($context) {
    $pos = position($context);
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
	comments($frame, $context);
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

    return $pos($data);
  }

  /**
   * Parse supports.
   */

  function atsupports($context) {
    $pos = position($context);
    $m = match('/^@supports *([^{]+)/s', $context);

    if (!$m) return;
    $supports = trim($m[1]);

    if (!open($context)) return error("@supports missing '{'", $context);

	$style = [];
    $style = array_merge(comments($style, $context), rules($context));

	if (!close($context)) return error("@supports missing '}'", $context);
	
	$data = [
      'type' => 'supports',
      'supports' => $supports,
	  'rules' => $style
	];

	settype($data, 'object');

    return $pos($data);
  }

  /**
   * Parse host.
   */

  function athost($context) {
    $pos = position($context);
    $m = match('/^@host\s*/s', $context);

    if (!$m) return;

    if (!open($context)) return error("@host missing '{'", $context);

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
   * Parse media.
   */

  function atmedia($context) {
    $pos = position($context);
    $m = match('/^@media *([^{]+)/s', $context);

    if (!$m) return;
    $media = trim($m[1]);

    if (!open($context)) return error("@media missing '{'", $context);

	$style = [];
    $style = array_merge(comments($style, $context), rules($context));

    if (!close($context)) return error("@media missing '}'", $context);

	$data = [

      'type' => 'media',
      'media' => $media,
      'rules' => $style
	];

	settype($data, 'object');

    return $pos($data);
  }


  /**
   * Parse custom-media.
   */

  function atcustommedia($context) {
    $pos = position($context);
    $m = match('/^@custom-media\s+(--[^\s]+)\s*([^{;]+);/s', $context);
	if (!$m) return;
	
	$data = [
      'type' => 'custom-media',
      'name' => trim($m[1]),
      'media' => trim($m[2])
    ];

	settype($data, 'object');

    return $pos($data);
  }

  /**
   * Parse paged media.
   */

  function atpage($context) {
    $pos = position($context);
    $m = match('/^@page */', $context);
    if (!$m) return;

    $sel = selector($context) || [];

	if (!open($context)) return error("@page missing '{'", $context);
	$decls = [];
    $decls = comments($decls, $context);

    // declarations
 //   $decl;
    while ($decl = declaration($context)) {
	  $decls[] = $decl;
	  comments($decls, $context);
    }

	if (!close($context)) return error("@page missing '}'", $context);
	
	$data = [

      'type' => 'page',
      'selectors' => $sel,
      'declarations' => $decls
	];

	settype($data, 'object');

    return $pos($data);
  }

  /**
   * Parse document.
   */

  function atdocument($context) {
    $pos = position($context);
    $m = match('/^@([-\w]+)?document *([^{]+)/s', $context);
    if (!$m) return;

    $vendor = trim($m[1]);
    $doc = trim($m[2]);

    if (!open($context)) return error("@document missing '{'", $context);

	$style = [];
    $style = array_merge(comments($style, $context), rules($context));

    if (!close($context)) return error("@document missing '}'", $context);

	$data = [
		
      'type' => 'document',
      'document' => $doc,
      'vendor' => $vendor,
      'rules' => $style
	];

	settype($data, 'object');

    return pos($data);
  }

  /**
   * Parse font-face.
   */

  function atfontface($context) {
    $pos = position($context);
    $m = match('/^@font-face\s*/s', $context);
    if (!$m) return;

	if (!open($context)) return error("@font-face missing '{'", $context);
	$decls = [];
    $decls = comments($decls, $context);

    // declarations
  //  $decl;
    while ($decl = declaration($context)) {
	  $decls[] = $decl;
	  comments($decls, $context);
    }

	if (!close($context)) return error("@font-face missing '}'", $context);
	
	$data = [
		
      'type' => 'font-face',
      'declarations' => $decls
	];

	settype($data, 'object');

    return $pos($data);
  }

  function _compileAtrule($name, $context) {
	//  $re = new RegExp('^@' + name + '\s*([^;]+);');
	  return function() use($name, $context) {
		$pos = position($context);
		$m = match('#^@'.$name .'\s*([^;]+);#', $context);
		if (!$m) return;
		$ret = ['type' => $name];
		$ret[$name] = trim($m[1]);
		
		settype($ret, 'object');
  
		return $pos($ret);
	  };
	}
  
  /**
   * Parse import
   */

  function atimport($context) {
	
   	return \call_user_func( _compileAtrule('import', $context), $context);
  }
  
  /**
   * Parse charset
   */

  function atcharset($context) {
	
		return \call_user_func( _compileAtrule('charset', $context), $context);
	}
 
  /**
   * Parse namespace
   */
  function atnamespace($context) {
	
	return \call_user_func( _compileAtrule('namespace', $context), $context);
}

  /**
   * Parse non-block at-rules
   */


  /**
   * Parse at rule.
   */

  function atrule($context) {
    if ($context->css[0] != '@') return;

    ($res = atimport($context))
      || ($res = atcharset($context))
      || ($res = atkeyframes($context))
	  || ($res = atfontface($context)) 
      || ($res = atsupports($context))
      || ($res = atmedia($context))
      || ($res = atcustommedia($context))
      || ($res = atnamespace($context))
      || ($res = atdocument($context))
      || ($res = atpage($context))
      || ($res = athost($context));
	  
	  return $res;
  }

  /**
   * Parse rule.
   */

  function rule($context) {
	$pos = position($context);
	
    $sel = selector($context);

	if (!$sel) return error('selector missing', $context);
	$c = [];
	comments($c, $context);
	
	$data = [
		
      'type' => 'rule',
      'selectors' => $sel,
      'declarations' => declarations($context)
	];

	settype($data, 'object');

    return $pos($data);
  }

  return stylesheet($context);
};
