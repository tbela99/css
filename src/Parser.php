<?php

namespace TBela\CSS;

require_once __DIR__.'/compat.php';


use Exception;
use Generator;
use ReflectionException;
use RuntimeException;
use TBela\CSS\Exceptions\IOException;
use TBela\CSS\Interfaces\ParsableInterface;
use TBela\CSS\Interfaces\RuleListInterface;
use TBela\CSS\Interfaces\ValidatorInterface;
use TBela\CSS\Parser\Helper;
use TBela\CSS\Parser\Lexer;
use TBela\CSS\Parser\ParserTrait;
use TBela\CSS\Parser\SyntaxError;
use TBela\CSS\Process\Exceptions\UnhandledException;
use TBela\CSS\Process\Pool as ProcessPool;

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
		'multi_processing_threshold' => 64 * 1024,
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

	protected $processPoolEvents = [];

	protected $queue = [];

	/**
	 * Parser constructor.
	 * @param string $css
	 * @param array $options
	 * @param string $media
	 * @throws Exception
	 */
	public function __construct($css = '', array $options = [], $media = '')
	{
		$this->setOptions($options);
		$this->lexer = (new Lexer())->
		setParentOffset((object)[

			'line' => $this->options['ast_position_line'],
			'column' => $this->options['ast_position_column'],
			'index' => $this->options['ast_position_index'],
			'src' => $this->options['ast_src']
		]);

		if ($css !== '') {

			$this->setContent($css, $media);
		}
	}

	/**
	 * @param string $event parse event name in ['enter', 'exit']
	 * @param callable $callable
	 * @return Parser
	 */
	public function on($event, callable $callable)
	{

		if (str_starts_with($event, 'pool.')) {

			$this->processPoolEvents[substr($event, 5)][] = $callable;

		} else {

			$this->lexer->on($event, $callable);
		}

		return $this;
	}

	/**
	 * @param string $event parse event name in ['enter', 'exit', 'start', 'end']
	 * @param callable $callable
	 * @return Parser
	 */
	public function off($event, callable $callable)
	{

		if (str_starts_with($event, 'pool.')) {

			$event = substr($event, 5);

			if (isset($this->processPoolEvents[$event])) {

				$this->processPoolEvents[$event] = array_filter($this->processPoolEvents[$event], function ($val) use ($callable) {

					return $val != $callable;
				});

				if (empty($this->processPoolEvents[$event])) {

					unset($this->processPoolEvents[$event]);
				}
			}

		} else {

			$this->lexer->on($event, $callable);
		}

		return $this;
	}

	/**
	 * @param string $content
	 * @param object $root
	 * @param string $file
	 * @return void
	 * @throws UnhandledException
	 * @throws ReflectionException
	 */
	public function parallelize($content, $root, $file)
	{
		$data = [];

		$processPool = (new ProcessPool());

		foreach ($this->processPoolEvents as $event => $callables) {

			foreach ($callables as $callable) {

				$processPool->on($event, $callable);
			}
		}

		$len = strlen($content);
		$options = $this->options;

		// min  65k
		$size = min($this->options['multi_processing_threshold'], $len / 2);
		$options['multi_processing'] = false;

		foreach ($this->slice($content, $size, $root->location->end) as $slice) {

			$processPool->add(function () use ($file, $slice, $options) {

				$parser = new Parser($slice[0], array_merge($options, [
					'ast_src' => $file,
					'ast_position_line' => $slice[1]->line,
					'ast_position_column' => $slice[1]->column,
					'ast_position_index' => $slice[1]->index
				]));

				$ast = $parser->getAst();

				return isset($ast->children) ? $ast->children : [];
			})->
			then(function (array $result, $index) use (&$data) {

				$data[$index] = $result;
			});
		}

		$processPool->wait();

		ksort($data);

		if (!isset($root->children)) {

			$root->children = [];
		}

		foreach ($data as $datum) {

			array_splice($root->children, count($root->children), 0, $datum);
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
	 * @param string $file
	 * @param string $media
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

		$this->queue[] = [
			'content' => $content,
			'media' => $media !== '' && $media != 'all' ? $media : '',
			'file' => $file
		];

		return $this;
	}

	/**
	 * @throws ReflectionException
	 * @throws UnhandledException
	 * @throws SyntaxError
	 * @throws Exception
	 */
	protected function doParse()
	{

		if (empty($this->queue)) {

			return;
		}

		$this->lexer->emit('start', $this->getContext());

//		$multi;
		foreach ($this->queue as $data) {

			$file = isset($data['file']) ? $data['file'] : '';
			$media = isset($data['media']) ? $data['media'] : '';

			$rule = null;
			$context = null;
			$content = $data['content'];

			if ($media !== '' && $media != 'all') {

				$rule = (object)[

					'type' => 'AtRule',
					'name' => 'media',
					'value' => Value::parse($media)
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

				$rule->src = $this->options['ast_src'];
			}

			$context = $this->lexer->createContext();

			if ($file !== '') {

				$newContext = $this->lexer->setParentOffset((object)[
					'line' => 1,
					'column' => 1,
					'index' => 0,
					'src' => $file
				])->createContext();

				$context = $newContext;
			}

			if (is_null($this->ast)) {

				$this->ast = $context;
			}

			if ($rule) {

				$this->ast->children[] = $rule;
				$this->pushContext($rule);
			}

			if (ProcessPool::isSupported() && $this->options['multi_processing'] && strlen($content) > $this->options['multi_processing_threshold']) {

				$this->parallelize($content, $this->getContext(), $file);
			} else {

				$this->lexer->setContent($content)->setContext($context);
				$this->tokenize();
			}

			if ($rule) {

				$this->popContext();
			}

			if (!empty($this->ast->children)) {

				$this->parseImport();
				$this->deduplicate($this->ast, $this->lastDedupIndex);
				$this->lastDedupIndex = max(0, count($this->ast->children) - 2);
			}
		}

		$this->lexer->emit('end', $this->getContext());
		$this->queue = [];
	}

	/**
	 * parse css and append to the existing AST
	 * @param string $css
	 * @param string $media
	 * @return Parser
	 */
	public function appendContent($css, $media = '')
	{

		$this->queue[] = [

			'file' => $this->options['ast_src'],
			'content' => $css,
			'media' => $media !== '' && $media != 'all' ? $media : ''
		];

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
	 * @param string $css
	 * @param string $media
	 * @return Parser
	 * @throws Exception
	 */
	public function setContent($css, $media = '')
	{

		$this->reset()->appendContent($css, $media);

		return $this;
	}

	protected function reset()
	{

		$this->ast = null;
		$this->errors = [];
		$this->context = [];
		$this->queue = [];
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


		if (!empty($this->queue)) {

			$this->doParse();
		}

		if (!empty($this->ast->children)) {

			$this->deduplicate($this->ast, $this->lastDedupIndex);
			$this->lastDedupIndex = max(0, count($this->ast->children) - 2);
		}

		if (is_null($this->ast)) {

			$this->ast = $this->lexer->createContext();
		}

		return $this->ast;
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

						$imports[$i] = $child;
					}

					continue;
				}

				if ($child->type != 'Comment') {

					break;
				}
			}

			if ($imports) {

				$pool = null;

				krsort($imports);

				$options = $this->options;

				foreach ($imports as $token) {

					preg_match('#^((["\']?)([^\\2]+)\\2)(.*?$)#', is_array($token->value) ? Value::renderTokens($token->value) : $token->value, $matches);

					$media = isset($matches[4]) ? trim($matches[4]) : '';

					if ($media == 'all') {

						$media = '';
					}

					$file = Helper::absolutePath($matches[3], isset($token->src) ? dirname($token->src) : '');

					if (count($imports) > 2 && ProcessPool::isSupported()) {

						if (!isset($pool)) {

							$pool = new ProcessPool();
						}

						$pool->add(function () use ($file, $options) {

							$ast = (new Parser('', $options))->load($file)->getAst();

							return isset($ast->children) ? $ast->children : [];
						})->then(function ($result, $index, $stderr, $exitCode /*, $thread */) use ($media, $token, $file) {

							if ($exitCode != 0) {

								throw new RuntimeException(sprintf("cannot resolve @import#%s (exit code #%s\n%s", $file, $exitCode, $stderr));
							}

							$token->children = $result;
							$token->value = $media;

							unset($token->isLeaf);

							$token->name = 'media';

						});


					} else {

						$parser = new static('', $this->options);

						$parser->ast = $token;
						$parser->append($file);

						$ast = $parser->getAst();
						$token->children = isset($ast->children) ? $ast->children : [];
						$token->value = $media;

						unset($token->isLeaf);

						$token->name = 'media';

					}
				}

			
				if (is_callable([$pool, 'wait'])) {

					$pool->wait();
				}

				foreach ($imports as $key => $token) {

					if (empty($token->value)) {

						array_splice($this->ast->children, $key, 1, isset($token->children) ? $token->children : []);
					}
				}
			}
		}
	}

	/**
	 * @param object $ast
	 * @param int|null $index
	 * @return object
	 * @throws Exception
	 */
	public function deduplicate($ast, $index = null)
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
	 * @throws Exception
	 * @ignore
	 */
	protected function computeSignature($ast)
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
	 * @param object $ast
	 * @param int|null $index
	 * @return object
	 * @throws Exception
	 */
	protected function deduplicateRules($ast, $index = null)
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
	 * @param object $ast
	 * @return object
	 * @throws Exception
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
	 * @param object $token
	 * @param object $parentRule
	 * @param object $parentStylesheet
	 * @return int
	 * @ignore
	 */
	protected function validate($token, $parentRule, $parentStylesheet) 
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
	protected function pushContext($context)
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

		array_pop($this->context);
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
	protected function enterNode($token, $parentRule, $parentStylesheet) 
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
	 * @ignore
	 */
	protected function exitNode($token)
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
//			array_splice($context->children, count($context->children), 0, $token->children);
			$context->children = array_merge($context->children, $token->children);

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
	protected function doValidate($token, $context, $parentStylesheet) 
	{
		$status = $this->validate($token, $context, $parentStylesheet);

		if ($status == ValidatorInterface::REJECT) {

			$this->handleError(sprintf("%s: %s at %s:%s:%s\n", $token->type, $this->error, isset($token->src) ? $token->src : '', $token->location->start->line, $token->location->start->column));
		}

		return $status;
	}

	public function slice($css, $size, $position) 
	{

		$i = -1; // ($position->index ?? 0) - 1;
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
				while ($k < $j && static::is_whitespace($buffer[$k])) {

					$k++;
				}

				if ($k > 0) {

					$this->update($position, substr($buffer, 0, $k));
					$position->index += $k;

					$buffer = substr($buffer, $k);
				}

				$pos = clone $position;

				$pos->index = max(0, $pos->index - 1);

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
			$pos->index = max(0, $pos->index - 1);

			yield [$buffer, $pos];
			$this->update($position, $buffer);
			$position->index += strlen($buffer);
		}
	}

	public function __toString()
	{


		try {

			if (empty($this->ast)) {

				if (empty($this->queue)) {

					return '';
				}

				if ((count($this->queue) > 1 || (strlen($this->queue[0]['content']) > $this->options['multi_processing_threshold'] * .8) && ProcessPool::isSupported() && $this->options['multi_processing'])) {

					$processPool = new ProcessPool();

					$options = $this->options;
					$web = PHP_SAPI != 'cli';
					$currentDirectory = Helper::getCurrentDirectory();
					$css = [];

					$options['multi_processing'] = false;
					$threshold = $options['multi_processing_threshold'];

					foreach ($this->queue as $data) {

						$file = isset($data['file']) ? $data['file'] : '';
						$content = $data['content'];
						$media = isset($data['media']) ? $data['media'] : '';

						$root = $currentDirectory == '/' ? '/' : $currentDirectory . '/';
						$size = min($threshold, strlen($content) / 2);

						foreach ($this->slice($content, $size, (object)[
							'line' => 1,
							'column' => 1,
							'index' => 0,
							'src' => $file
						]) as $slice) {

							$processPool->add(function () use ($root, $currentDirectory, $web, $media, $file, $slice, $options) {

								$parser = (new Parser($slice[0], array_merge($options, [
									'ast_src' => $file,
									'ast_position_line' => $slice[1]->line,
									'ast_position_column' => $slice[1]->column,
									'ast_position_index' => $slice[1]->index
								]), $media));

								$renderer = new Renderer();

								$ast = $parser->getAst();

								$children = isset($ast->children) ? $ast->children : [];
								$result = [];

								foreach ($children as $child) {

									$css = $renderer->renderAst($child);

									if ($css !== '') {

										if ($child->type == 'Comment') {

											$result[] = (object)[
												'type' => $child->type,
												'css' => $css
											];
											continue;
										}

										if (isset($result[$css])) {

											unset($result[$css]);
										}

										$result[$css] = (object)[
											'type' => $child->type,
											'css' => $css
										];
									}
								}

								return array_values($result);
							})->
							then(function (array $result, $index) use (&$css) {

								$css[$index] = $result;
							});
						}
					}

					$processPool->wait();

					ksort($css);

					$result = [];

					foreach ($css as $data) {

						foreach ($data as $datum) {

							if ($datum->type == 'Comment') {

								$result[] = $datum->css;
								continue;
							}

							if (isset($result[$datum->css])) {

								unset($result[$datum->css]);
							}

							$result[$datum->css] = $datum->css;
						}
					}

					return implode((new Renderer())->getOptions('glue'), $result);
				}
			}

			$this->doParse();

			return (new Renderer())->renderAst($this->ast);

		} catch (Exception $ex) {

			error_log($ex);
		}

		return '';
	}

	/**
	 * @throws Exception
	 */
	protected function tokenize()
	{
		foreach ($this->lexer->tokenize() as $event => $data) {

			$token = $data[0];
			$status = call_user_func_array([$this, $event . 'Node'], $data);

			if ($event == 'enter' && $status != ValidatorInterface::VALID) {

				$token->type = 'Invalid' . $token->type;
			}
		}
	}
}