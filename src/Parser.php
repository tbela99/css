<?php

namespace TBela\CSS;

require_once __DIR__.'/compat.php';

use Exception;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use TBela\CSS\Exceptions\IOException;
use TBela\CSS\Interfaces\ParsableInterface;
use TBela\CSS\Interfaces\RuleListInterface;
use TBela\CSS\Interfaces\ValidatorInterface;
use TBela\CSS\Parser\Helper;
use TBela\CSS\Parser\Lexer;
use TBela\CSS\Parser\MultiprocessingTrait;
use TBela\CSS\Parser\ParserTrait;
use TBela\CSS\Parser\SyntaxError;
use TBela\CSS\Process\Pool;

/**
 * Css Parser
 * @package TBela\CSS
 * ok */
class Parser implements ParsableInterface
{

	use ParserTrait, MultiprocessingTrait;

	/**
	 * @var ValidatorInterface[]
	 */
	protected static $validators = [];

	protected $context = [];
	protected $lexer;

	protected $errors = [];
	protected $error;

	protected $ast = null;

	/**
	 * enable multiprocessing if the css is larger than CSS_MAX_SIZE
	 */

	/**
	 * @var array
	 * @ignore
	 */
	protected $options = [
		'capture_errors' => true,
		'flatten_import' => false,
		'allow_duplicate_rules' => ['font-face'], // set to true for speed
		'allow_duplicate_declarations' => true,
		'multi_processing' => true,
		// buffer size 32k. higher values may break the execution
		'multi_processing_threshold' => 32768,
//        'children_process' => 20,
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
	protected $lastDedupIndex = null;

	/**
	 * @var serialize | json
	 */
	// TODO: remove serialize as it seams to produce warning
	protected $format = 'serialize';

	/**
	 * Parser constructor.
	 * @param $css
	 * @param array $options
	 * @throws SyntaxError
	 */
	public function __construct($css = '', array $options = [])
	{
		$this->setOptions($options);
		$this->lexer = (new Lexer())->
		setParentOffset((object)[

			'line' => $this->options['ast_position_line'],
			'column' => $this->options['ast_position_column'],
			'index' => $this->options['ast_position_index'],
			'src' => $this->options['ast_src']
		])->
		on('enter', function () {

			return call_user_func_array([$this, 'enterNode'], func_get_args());
		})->
		on('exit', function () {

			return call_user_func_array([$this, 'exitNode'], func_get_args());
		});

		if ($css !== '') {

			$this->setContent($css);
		}
	}

	/**
	 * @param $event parse event name in ['enter', 'exit']
	 * @param callable $callable
	 * @return Parser
	 */
	public function on($event, callable $callable)
	{

		$this->lexer->on($event, $callable);
		return $this;
	}

	/**
	 * @param $event parse event name in ['enter', 'exit', 'start', 'end']
	 * @param callable $callable
	 * @return Parser
	 */
	public function off($event, callable $callable)
	{

		$this->lexer->off($event, $callable);
		return $this;
	}

	public function getCliArgs(array $options, $src, $position)
	{

		$args = [
			'-ac',
			'--parse-multi-processing=off',
			sprintf('--parse-ast-src=%s', $src)
		];

		if (!empty($position)) {

			array_push($args,
				sprintf('--parse-ast-position-index=%s', max(0, $position->index - 1)),
				sprintf('--parse-ast-position-line=%s', $position->line),
				sprintf('--parse-ast-position-column=%s', $position->column));
		}

		if (!$options['capture_errors']) {
			// default is on
			$args[] = '--capture-errors=off';
		}

		foreach ([
					 'capture_errors' => true,
					 'flatten_import' => false,
					 'allow_duplicate_rules' => ['font-face'], // set to true for speed
					 'allow_duplicate_declarations' => true,
					 'multi_processing' => true,
					 // 65k
					 'multi_processing_threshold' => 66560,
//        'children_process' => 20,
					 'ast_src' => '',
					 'ast_position_line' => 1,
					 'ast_position_column' => 1,
					 'ast_position_index' => 0
				 ] as $key => $value) {

			if (in_array($key, ['multi_processing', 'multi_processing_threshold', 'ast_src', 'ast_position_line', 'ast_position_column', 'ast_position_index'])) {

				continue;
			}

			if ($key == 'allow_duplicate_rules') {

				if (!$options['allow_duplicate_rules']) {
					// default is on
					$args[] = '--parse-allow-duplicate-rules==off';
				}
			} else if ($key == 'allow_duplicate_declarations') {

				if (!$options['allow_duplicate_declarations']) {
					// default is on
					$args[] = '--parse-allow-duplicate-declarations==off';
				}
			} else if (isset($options[$key]) && $options[$key] !== $value) {

				$args[] = sprintf('--%s=%s', str_replace('_', '-', $key), is_bool($options[$key]) ? ($options[$key] ? 'on' : 'off') : $options[$key]);
			}
		}


//		if ($options['flatten_import']) {
//			// default is off
//			$args[] ='--flatten-import=on';
//		}
//
//		if (!$options['allow_duplicate_declarations']) {
//			// default is on
//			$args[] ='--parse-allow-duplicate-declarations==off';
//		}

		$args[] = sprintf('--output-format=%s', $this->format);

		return $args;
	}

	/**     * @param $content
	 * @param object $root
	 * @param $file
	 * @return void
	 */
	protected function stream($content, \stdClass $root, $file)
	{

		$file = Helper::absolutePath($file, Helper::getCurrentDirectory());
		$size = max(min($this->options['multi_processing_threshold'], strlen($content) / 2), 1);

		foreach ($this->slice($content, $size, $root->location->end) as $index => $data) {

			$this->enQueue($data[0], $this->getCliArgs($this->options, $file, $data[1]));
		}

		$this->pool->wait();

		if (!empty($this->output)) {

			if (!isset($root->children)) {

				$root->children = [];
			}

			foreach ($this->output as $token) {

				if (isset($token->children)) {

					array_splice($root->children, count($root->children), 0, $token->children);
				}
			}

			$this->output = [];
		}
	}

	/**
	 * @param Exception $error
	 * @return void
	 */
	protected function emit(Exception $error)
	{
		$this->lexer->emit('error', $error);
	}

	/**
	 * parse css file and append to the existing AST
	 * @param $file
	 * @param $media
	 * @return Parser
	 * @throws Exception
	 */
	public function append($file, $media = '')
	{

		$file = Helper::absolutePath($file, Helper::getCurrentDirectory());

		if (!preg_match('#^(https?:)?//#', $file) && is_file($file)) {

			$content = file_get_contents($file);

		} else {

			$content = Helper::fetchContent($file);
		}

		if ($content === false) {

			throw new IOException(sprintf('File Not Found "%s" => \'%s:%s:%s\'', $file, isset($context->location->src) ? $context->location->src : null, isset($context->location->end->line) ? $context->location->end->line : null, isset($context->location->end->column) ? $context->location->end->column : null), 404);
		}

		$rule = null;

		$newContext = $this->lexer->setParentOffset((object)[
			'line' => 1,
			'column' => 1,
			'index' => 0,
			'src' => $file
		])->createContext();

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

		if (function_exists('\\proc_open') && $this->options['multi_processing'] && strlen($content) > $this->options['multi_processing_threshold']) {

			$root = $this->getContext();
			$this->stream($content, $root, $file);
		} else {

			$this->lexer->setContent($content)->setContext($newContext)->tokenize();
		}

		if ($rule) {

			$this->popContext();
		}

		if (!empty($this->ast->children)) {

			$this->parseImport();
			$this->deduplicate($this->ast, $this->lastDedupIndex);
			$this->lastDedupIndex = max(0, count($this->ast->children) - 2);
		}

		return $this;
	}

	/**
	 * parse css and append to the existing AST
	 * @param $css
	 * @param $media
	 * @return Parser
	 * @throws SyntaxError|Exception
	 */
	public function appendContent($css, $media = '')
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

		if (function_exists('\\proc_open') && $this->options['multi_processing'] && strlen($css) > $this->options['multi_processing_threshold']) {

			$this->stream($css, $this->getContext(), $this->options['ast_src']);
		} else {

			$this->lexer->
			setContent($css)->
			tokenize();
		}

		if (!empty($this->ast->children)) {

			$this->parseImport();
			$this->deduplicate($this->ast, $this->lastDedupIndex);
			$this->lastDedupIndex = max(0, count($this->ast->children) - 2);
		}

		return $this;
	}

	/**
	 * @param Parser $parser
	 * @return Parser
	 * @throws SyntaxError
	 */
	public function merge(Parser $parser)
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
	 * @param $css
	 * @param $media
	 * @return Parser
	 * @throws SyntaxError
	 */
	public function setContent($css, $media = '')
	{

		if ($media !== '' && $media != 'all') {

			$css = '@media ' . $media . '{ ' . rtrim($css) . ' }';
		}

		$this->reset()->appendContent($css);

		return $this;
	}

	protected function reset()
	{

		$this->ast = null;
		$this->errors = [];
		$this->context = [];
		$this->lastDedupIndex = null;

		return $this;
	}

	/**
	 * load css content from a file
	 * @param $file
	 * @param $media
	 * @return Parser
	 * @throws Exceptions\IOException|Exception
	 */

	public function load($file, $media = '')
	{

		return $this->reset()->append($file, $media);
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

				if ($key == 'ast_src') {

					$this->options['ast_src'] = empty($options[$key]) ? '' : Helper::absolutePath($options[$key], Helper::getCurrentDirectory());
					continue;
				}

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

	public function getOptions($name = null)
	{

		return is_null($name) ? $this->options : (isset($this->options[$name]) ? $this->options[$name] : null);
	}

	/**
	 * parse Css
	 * @return RuleListInterface|null
	 * @throws SyntaxError
	 */
	public function parse()
	{


		$this->getAst();
		return Element::getInstance($this->ast);
	}

	/**
	 * @inheritDoc
	 * @throws SyntaxError|Exception
	 */
	public function getAst()
	{

		if (is_null($this->ast)) {

			$this->ast = $this->lexer->createContext();
			$this->lexer->setContext($this->ast)->tokenize();
		}

		if (!empty($this->ast->children)) {

			$this->deduplicate($this->ast, $this->lastDedupIndex);
			$this->lastDedupIndex = max(0, count($this->ast->children) - 2);
		}

		return $this->ast;
	}


	public function slice($css, $size, $position)
	{

		$i = (isset($position->index) ? $position->index : 0) - 1;
		$j = strlen($css) - 1;

		$buffer = '';

		while ($i++ < $j) {

			$string = Parser::substr($css, $i, $j, ['{']);

			if ($string === false) {

				$buffer = substr($css, $i);

				$pos = clone $position;
				$pos->index++;

				yield [$buffer, $pos];

				$this->update($position, $buffer);
				$position->index += strlen($buffer);

				$buffer = '';
				break;
			}

			$string .= Parser::_close($css, '}', '{', $i + strlen($string), $j);
			$buffer .= $string;

			if (strlen($buffer) >= $size) {

				$k = 0;
				while (static::is_whitespace($buffer[$k])) {

					$k++;
				}

				if ($k > 0) {

					$this->update($position, substr($buffer, 0, $k));
					$position->index += $k;

					$buffer = substr($buffer, $k);
				}

				$pos = clone $position;
				$pos->index++;

				yield [$buffer, $pos];

				$this->update($position, $buffer);
				$position->index += strlen($buffer);

				$buffer = '';
			}

			$i += strlen($string) - 1;
		}

		if ($buffer) {

			$k = 0;
			$l = strlen($buffer);
			while ($k < $l && Parser::is_whitespace($buffer[$k])) {

				$k++;
			}

			if ($k > 0) {

				$this->update($position, substr($buffer, 0, $k));
				$position->index += $k;

				$buffer = substr($buffer, $k);
			}
		}

		if (trim($buffer) !== '') {

			$pos = clone $position;
			$pos->index++;

			yield [$buffer, $pos];
			$this->update($position, $buffer);
			$position->index += strlen($buffer) - 1;
		}

		$position->index = max(0, $position->index - 1);
		$position->column = max(1, $position->column - 1);
	}

	/**
	 * @throws SyntaxError
	 * @throws Exception
	 */
	protected function parseImport()
	{

		if ($this->options['flatten_import']) {

			$imports = [];

			$j = count($this->ast->children);

			for ($i = 0; $i < $j; $i++) {

				$child = $this->ast->children[$i];

				if ($child->type == 'AtRule' && in_array($child->name, ['import', 'charset'])) {

					if ($child->name == 'import') {

						$imports[$i] = clone $child;
					}

					continue;
				}

				if ($child->type != 'Comment') {

					break;
				}
			}

			if ($imports) {

				$pool = null;

				$phpExe = (new PhpExecutableFinder())->find();

				foreach (array_reverse($imports, true) as $key => $token) {

					preg_match('#^((["\']?)([^\\2]+)\\2)(.*?$)#', is_array($token->value) ? Value::renderTokens($token->value) : $token->value, $matches);

					$media = isset($matches[4]) ? trim($matches[4]) : '';

					if ($media == 'all') {

						$media = '';
					}

					$file = Helper::absolutePath($matches[3],  isset($token->src) ? dirname($token->src) : '');

					$token->value = $media;

					unset($token->isLeaf);

					$token->name = 'media';

					if (count($imports) > 2 && function_exists('\\proc_open')) {

						$args = $this->getCliArgs($this->options, $file, null);

						array_unshift($args, $phpExe, '-f', __DIR__ . '/../cli/css-parser', '--');

						$args[] = '--file=' . $file;
						$args[] = '--output-format=json';
						$args[] = '-c';

						$process = new Process($args);
						$process->setPty(true);

						if (!isset($pool)) {

							$pool = (new Pool());
						}

						$pool->add($process)->then(function (Process $process, $stdout, $stderr) use ($token, $file) {

							if ($process->getExitCode() != 0) {

								throw new \RuntimeException(sprintf("cannot resolve @import#%s\n%s", $file, $stderr));
							}

							if (!empty($stdout)) {

								$ast = /* $this->format == 'serialize' ? unserialize($payload) : */
									json_decode($stdout);

								$token->children = isset($ast->children) ? $ast->children : [];
							}
						});
					} else {

						$parser = new static('', $this->options);

						$parser->ast = $token;
						$parser->append($file);
					}
				}

				if ($pool) {

					$pool->wait();
				}

				foreach (array_reverse($imports, true) as $key => $token) {

					if (empty($token->value)) {

						array_splice($this->ast->children, $key, 1, isset($token->children ) ? $token->children  : []);
					} else {

						$this->ast->children[$key] = $token;
					}
				}
			}
		}
	}

	/**
	 * @param \stdClass $ast
	 * @param int|null $index
	 * @return object
	 * @throws Exception
	 */
	public function deduplicate(\stdClass $ast, $index = null)
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
	 * @param \stdClass $ast
	 * @return string
	 * @throws Exception
	 * @ignore
	 */
	protected function computeSignature(\stdClass $ast)
	{

		$signature = 'type:' . $ast->type;

		$name = isset($ast->name) ? $ast->name : null;

		if (isset($name)) {

			$signature .= ':name:' . $name;
		}

		$selector = isset($ast->selector) ? $ast->selector : null;

		if (isset($selector)) {

			$signature .= ':selector:' . (is_array($selector) ? implode(',', $selector) : $selector);
		}

		$vendor = isset($ast->vendor) ? $ast->vendor : null;

		if (isset($vendor)) {

			$signature .= ':vendor:' . $vendor;
		}

		if (in_array($ast->type, ['AtRule', 'NestingAtRule', 'NestingMediaRule'])) {

			$signature .= ':value:' . (is_array($ast->value) ? Value::renderTokens($ast->value) : $ast->value);
		}

		return $signature;
	}

	/**
	 * @param \stdClass $ast
	 * @param int|null $index
	 * @return object
	 * @throws Exception
	 */
	protected function deduplicateRules(\stdClass $ast, $index = null)
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
						if ($el->type == 'Comment' || $el->type == 'Declaration') {

							continue;
						}

						if (!in_array($el->type, ['Rule', 'AtRule'])) {

							continue;
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
	 * @param \stdClass $ast
	 * @return object
	 * @throws Exception
	 */
	protected function deduplicateDeclarations(\stdClass $ast)
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
	 * @param $message
	 * @param $error_code
	 * @return SyntaxError
	 * @throws SyntaxError
	 */
	protected function handleError($message, $error_code = 400)
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
	 * @param \stdClass $token
	 * @param \stdClass $parentRule
	 * @param \stdClass $parentStylesheet
	 * @return int
	 * @ignore
	 */
	protected function validate(\stdClass $token, \stdClass $parentRule, \stdClass $parentStylesheet)
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
	protected function getContext()
	{

		return end($this->context) ?: (isset($this->ast) ? $this->ast : $this->getAst());
	}

	/**
	 * push the current parent node
	 * @param \stdClass $context
	 * @return void
	 * @ignore
	 */
	protected function pushContext(\stdClass $context)
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
	 * @param \stdClass $token
	 * @param \stdClass $parentRule
	 * @param \stdClass $parentStylesheet
	 * @return int
	 * @throws SyntaxError
	 * @ignore
	 */
	protected function enterNode(\stdClass $token, \stdClass $parentRule, \stdClass $parentStylesheet)
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

				$this->handleError(sprintf('CDO token not allowed here %s %s:%s:%s', $token->type, isset($token->src) ? $token->src : '', $token->location->start->line, $token->location->start->column));
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
	 * parse event handler
	 * @param object $token
	 * @return void
	 * @throws SyntaxError
	 * @ignore
	 */
	protected function exitNode(\stdClass $token)
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
					is_string($token->value) &&
					$token->value == 'all'
				) ||
				(
					is_array($token->value) &&
					count($token->value) == 1 &&
					(isset($token->value[0]->value) ? $token->value[0]->value : '') == 'all'
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
	protected function doValidate(\stdClass $token, \stdClass $context, \stdClass $parentStylesheet)
	{
		$status = $this->validate($token, $context, $parentStylesheet);

		if ($status == ValidatorInterface::REJECT) {

			$this->handleError(sprintf("%s: %s at %s:%s:%s\n", $token->type, $this->error, isset($token->src) ? $token->src : '', $token->location->start->line, $token->location->start->column));
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