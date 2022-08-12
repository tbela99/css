<?php

namespace TBela\CSS;

use axy\sourcemap\SourceMap;
use Exception;
use Generator;
use TBela\CSS\Ast\Traverser;
use TBela\CSS\Exceptions\IOException;
use TBela\CSS\Interfaces\ParsableInterface;
use TBela\CSS\Interfaces\RenderableInterface;
use TBela\CSS\Interfaces\ElementInterface;
use TBela\CSS\Parser\Helper;
use TBela\CSS\Parser\MultiprocessingTrait;
use TBela\CSS\Parser\SyntaxError;
use TBela\CSS\Property\PropertyList;
use function is_string;

/**
 * Css node Renderer
 * @package TBela\CSS
 */
class Renderer
{

	use MultiprocessingTrait;

	protected $options = [
		'glue' => "\n",
		'indent' => ' ',
		'css_level' => 4,
		'separator' => ' ',
		'charset' => false,
		'compress' => false,
		'sourcemap' => false,
		'convert_color' => false,
		'remove_comments' => false,
		'preserve_license' => false,
		'legacy_rendering' => false,
		'compute_shorthand' => true,
		'remove_empty_nodes' => false,
		'allow_duplicate_declarations' => false,
		'multi_processing' => false,
		'multi_processing_threshold' => 400
	];

	/**
	 * @var string
	 * @ignore
	 */

	protected $outFile = '';
	protected $indents = [];
	protected $sourcemap;

	// 'serialize-array' or 'json-array'
	protected $format = 'serialize-array';

	/**
	 * Identity constructor.
	 * @param array $options
	 */

	public function __construct(array $options = [])
	{

		$this->setOptions($options);
	}

	public function getCliArgs(array $parseOptions, array $renderOptions)
	{

		$args = [
			'--parse-multi-processing=off'
		];

		if (empty($parseOptions['capture_errors'])) {
			$args[] = '--capture-errors=off';
		}
		if (!empty($parseOptions['ast_src'])) {
			// default is on
			$args[] = sprintf('--parse-ast-src=%s', $parseOptions['ast_src']);
		}

		if (!empty($parseOptions['flatten_import'])) {
			// default is off
			$args[] = '--flatten-import=on';
		}

		if (!empty($parseOptions['ast_src'])) {
			// default is off
			$args[] = '--parse-ast-src=' . $parseOptions['ast_src'];
		}

		if (empty($parseOptions['allow_duplicate_declarations'])) {
			// default is on
			$args[] = '--parse-allow-duplicate-declarations==off';
		}

		$args[] = '--render-multi-processing=off';

		foreach ([
//					 'glue' => "\n",
//					 'indent' => ' ',
//					 'css_level' => 4,
//					 'separator' => ' ',
					 'charset' => false,
					 'compress' => false,
//					 'sourcemap' => false,
					 'convert_color' => false,
					 'remove_comments' => false,
					 'preserve_license' => false,
					 'legacy_rendering' => false,
					 'compute_shorthand' => true,
					 'remove_empty_nodes' => false,
					 'allow_duplicate_declarations' => false,
					 'multi_processing' => true
				 ] as $key => $value) {

			if (!isset($renderOptions[$key]) || $renderOptions[$key] === $value) {

				continue;
			}

			$args[] = sprintf('--%s%s=%s', $key == 'allow_duplicate_declarations' ? 'render-' : '', str_replace('_', '-', $key), is_bool($renderOptions[$key]) ? ($renderOptions[$key] ? 'on' : 'off') : $renderOptions[$key]);
		}

		$args[] = '--output-format=' . $this->format;

		return $args;
	}

	public function slice($css, $size)
	{

		$i = -1;
		$j = strlen($css) - 1;

		$buffer = '';

		while ($i++ < $j) {

			$string = Parser::substr($css, $i, $j, ['{']);

			if ($string === false) {

				$buffer = substr($css, $i);

				yield $buffer;

				$buffer = '';
				break;
			}

			$string .= Parser::_close($css, '}', '{', $i + strlen($string), $j);
			$buffer .= $string;

			if (strlen($buffer) >= $size) {

				$k = 0;
				while (Parser::is_whitespace($buffer[$k])) {

					$k++;
				}

				if ($k > 0) {

					$buffer = substr($buffer, $k);
				}

				yield $buffer;
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

				$buffer = substr($buffer, $k);
			}
		}

		if (trim($buffer) !== '') {

			yield $buffer;
		}
	}

	/**
	 * @param string $css
	 * @param array $renderOptions
	 * @param array $parseOptions
	 * @return string
	 * @throws IOException
	 * @throws SyntaxError
	 * @throws Exception
	 */
	public static function fromString($css, array $renderOptions = [], array $parseOptions = [])
	{

		$parser = new Parser('', $parseOptions);
		$renderer = new static($renderOptions);
		$size = max(1, min($parser->getOptions('multi_processing_threshold'), strlen($css) / 2));

		if (function_exists('\\proc_open') && $renderer->options['multi_processing'] && empty($renderer->options['sourcemap']) && strlen($css) > $size) {

			$args = $renderer->getCliArgs($parser->getOptions(), $renderer->options);

			foreach ($renderer->slice($css, $size) as $buffer) {

				$renderer->enQueue($buffer, $args);
			}

			$renderer->pool->wait();

			$result = [];

			foreach ($renderer->output as $sets) {

				foreach ($sets as $key => $value) {

					if (isset($result[$key])) {

						unset($result[$key]);
					}

					$result[$key] = $value;
				}
			}

			$renderer->output = [];

			return implode($renderer->options['glue'], $result);
		}

		return $renderer->renderAst($parser->setContent($css));
	}

	/**
	 * @param string $file
	 * @param array $renderOptions
	 * @param array $parseOptions
	 * @return string
	 * @throws IOException
	 * @throws SyntaxError
	 */
	public static function fromFile($file, array $renderOptions = [], array $parseOptions = [])
	{

		$file = Helper::absolutePath($file, Helper::getCurrentDirectory());

		if (!preg_match('#^(https?:)?//#', $file) && is_file($file)) {

			$content = file_get_contents($file);

		} else {

			$content = Helper::fetchContent($file);
		}

		if ($content === false) {

			throw new IOException(sprintf('File Not Found "%s"', $file), 404);
		}

		return static::fromString($content, $renderOptions, array_merge($parseOptions, ['ast_src' => $file]));
	}

	/**
	 * render an ElementInterface or a Property
	 * @param RenderableInterface $element the element to render
	 * @param null|int $level indention level
	 * @param $parent render parent
	 * @return string
	 * @throws Exception
	 */

	public function render(RenderableInterface $element, $level = null, $parent = false)
	{

		if ($parent && ($element instanceof ElementInterface) && !is_null($element['parent'])) {

			$element = $element->copy()->getRoot();
		}

		return $this->renderAst($element->getAst(), $level);
	}

	/**
	 * @param \stdClass|ParsableInterface $ast
	 * @param int|null $level
	 * @return string
	 * @throws Exception
	 */

	public function renderAst($ast, $level = null)
	{

		$this->outFile = '';

		if ($ast instanceof ParsableInterface) {

			$ast = $ast->getAst();
		}

		if ($this->options['legacy_rendering']) {

			$ast = $this->flatten($ast);
		}

		$block_size = 1500;

		if (function_exists('\\proc_open') && $this->options['multi_processing'] && empty($this->options['sourcemap']) && $ast->type == 'Stylesheet' && isset($ast->children) && count($ast->children) > $block_size) {

			$args = $this->getCliArgs([], $this->options);

			$args[] = '--input-format=serialize';

			$j = count($ast->children);
			$i = 0;

			while ($i < $j) {

				$this->enQueue(serialize((object)['type' => 'Stylesheet', 'children' => array_slice($ast->children, $i, $block_size)]), $args);
				$i += $block_size;
			}

			$this->pool->wait();

			$result = [];

			foreach ($this->output as $sets) {

				foreach ($sets as $key => $value) {

					if (isset($result[$key])) {

						unset($result[$key]);
					}

					$result[$key] = $value;
				}
			}

			$this->output = [];

			return implode($this->options['glue'], $result);
		}

		switch ($ast->type) {

			case 'Stylesheet':
				return $this->renderCollection($ast, $level, null);

			case 'Rule':
			case 'AtRule':
			case 'Comment':
			case 'Property':
			case 'NestingRule':
			case 'Declaration':
			case 'NestingAtRule':
			case 'NestingMediaRule':

				return $this->{'render' . $ast->type}($ast, $level, null);
		}

		throw new Exception('Type not supported ' . $ast->type);
	}

	/**
	 * @param \stdClass|ParsableInterface $ast
	 * @param string $file
	 * @return Renderer
	 * @throws IOException
	 * @throws Exception
	 */

	public function save($ast, $file)
	{

		if ($ast instanceof ParsableInterface) {

			$ast = $ast->getAst();
		}

		if ($this->options['legacy_rendering']) {

			$ast = $this->flatten($ast);
		}

		$this->outFile = Helper::absolutePath($file, Helper::getCurrentDirectory());

		if ($this->options['sourcemap']) {

			$this->sourcemap = new SourceMap();
			$position = (object)[
				'line' => 0,
				'column' => 0
			];
			$map = $file . '.map';

			$result = (new Traverser())->
			on('exit', function ($node, $level) {

				if (isset($node->children)) {

					$node = clone $node;

					$renderTree = [];

					$i = count($node->children);

					while ($i--) {

						$child = $node->children[$i];
						$css = $this->{'render' . $child->type}($child, $level);

						if ($child->type == 'Comment') {

							$child->css = $css;
							$renderTree[] = $child;
						} else if ($css !== '' && !isset($renderTree[$css])) {

							$child->css = $css;
							$renderTree[$css] = $child;
						} else {

							array_splice($node->children, $i, 1);
						}
					}

					$node->renderTree = array_reverse($renderTree);
				}

				return $node;

			})->traverse($ast);

			$css = '';
			foreach ($result->renderTree as $child) {

				$css .= $child->css . $this->options['glue'];
			}

			$this->walk($result->renderTree, $position);
			$json = $this->sourcemap->getData();

			$this->sourcemap = null;

			if (file_put_contents($map, json_encode($json)) === false) {

				throw new IOException("cannot write map into $map", 500);
			}

			if ($css !== '') {

				$css = rtrim($css) . "\n/*# sourceMappingURL=" . Helper::relativePath($map, dirname($file)) . " */";
			}

		} else {

			$css = $this->{'render' . $ast->type}($ast, null);
		}

		if (file_put_contents($file, $css) === false) {

			throw new IOException("cannot write output into $file", 500);
		}

		return $this;
	}

	/**
	 * @param array $tree
	 * @param object $position
	 * @param int|null $level
	 * @return void
	 * @throws Exception
	 * @ignore
	 */
	protected function walk(array $tree, \stdClass $position, $level = 0)
	{

		$pos = clone $position;

		foreach ($tree as $node) {

			if ($level) {

				$this->update($pos, $this->indents[$level]);
			}

			switch ($node->type) {

				case 'Comment':

					$this->update($pos, $node->css . $this->options['glue']);
					break;

				case 'Property':
				case 'Declaration':

					if (!$this->options['legacy_rendering']) {

						$this->update($pos, $node->css . ';' . $this->options['glue']);
					}

					break;

				case 'AtRule':
				case 'NestedMediaRule':

					if ($node->name == 'media' && (!isset($node->value) || $node->value == '' || $node->value == 'all')) {

						$this->walk($node->renderTree, clone $pos, $level);
						break;
					}

					$this->addPosition($pos, $node);

					if (isset($node->children) || !empty($node->isLeaf)) {

						$this->update($pos, substr($node->css . $this->options['glue'], $level + 1));
					} else {

						$clone = clone $pos;
						$this->update($clone, $this->renderAtRuleMedia($node, $level) . $this->options['indent'] . '{' . $this->options['glue']);
						$this->walk($node, $clone, $level + 1);
						$this->update($pos, substr($node->css . $this->options['glue'], $level));
					}

					break;

				case 'Rule':
				case 'NestingRule':
				case 'NestingAtRule':

					$this->addPosition($pos, $node);

					$clone = clone $pos;
					$this->update($clone, $this->renderSelector($node, $level) . $this->options['indent'] . '{' . $this->options['glue']);
					$this->walk($node->renderTree, $clone, is_null($level) ? 0 : $level + 1);
					$this->update($pos, substr($node->css . $this->options['glue'], $level));
					break;
			}

			$this->update($position, substr($node->css . $this->options['glue'], $level));
		}
	}

	/**
	 * @param object $ast
	 * @param int|null $level
	 * @return string
	 * @ignore
	 */

	protected function renderStylesheet(\stdClass $ast, $level)
	{

		return $this->renderCollection($ast, $level);
	}

	/**
	 * render a rule
	 * @param \stdClass $ast
	 * @param int|null $level
	 * @return string
	 * @throws Exception
	 * @ignore
	 */

	protected function renderRule(\stdClass $ast, $level)
	{

		settype($level, 'int');
		$result = $this->renderSelector($ast, $level);
		$output = $this->renderCollection($ast, $level + 1);

		if ($output === '' && $this->options['remove_empty_nodes']) {

			return '';
		}

		return $result . $this->options['indent'] . '{' .
			$this->options['glue'] .
			$output . $this->options['glue'] .
			$this->indents[$level] .
			'}';
	}

	/**
	 * render a rule
	 * @param \stdClass $ast
	 * @param int|null $level
	 * @return string
	 * @throws Exception
	 * @ignore
	 */

	protected function renderAtRuleMedia(\stdClass $ast, $level)
	{

		$output = '@' . $this->renderName($ast);
		$value = isset($ast->value) && $ast->value != 'all' ? $this->renderValue($ast) : '';

		if ($value !== '') {

			if ($this->options['compress'] && $value[0] == '(') {

				$output .= $value;
			} else {

				$output .= rtrim($this->options['separator'] . $value);
			}
		}

		settype($level, 'int');

		if (!isset($this->indents[$level])) {

			$this->indents[$level] = str_repeat($this->options['indent'], $level);
		}

		$indent = $this->indents[$level];

		if (!empty($ast->isLeaf)) {

			return $indent . $output . ';';
		}

		return $indent . $output;
	}

	/**
	 * render at-rule
	 * @param \stdClass $ast
	 * @param int|null $level
	 * @return string
	 * @throws Exception
	 * @ignore
	 */

	protected function renderAtRule(\stdClass $ast, $level)
	{

		if ($ast->name == 'charset' && !$this->options['charset']) {

			return '';
		}

		settype($level, 'int');
		$media = $this->renderAtRuleMedia($ast, $level);

		if ($media === '' || !empty($ast->isLeaf)) {

			return $media;
		}

		if ($ast->name == 'media' && (!isset($ast->value) || $ast->value == 'all')) {

			return $this->renderCollection($ast, $level);
		}

		$elements = $this->renderCollection($ast, $level + 1);

		if ($elements === '' && !empty($this->options['remove_empty_nodes'])) {

			return '';
		}

		return $media . $this->options['indent'] . '{' . $this->options['glue'] . $elements . $this->options['glue'] . $this->indents[$level] . '}';
	}

	/**
	 * render a list
	 * @param \stdClass $ast
	 * @param int|null $level
	 * @return string
	 * @ignore
	 */

	protected function renderCollection(\stdClass $ast, $level)
	{

		$type = $ast->type;
		$glue = ($type == 'Rule' || ($type == 'AtRule' && !empty($ast->hasDeclarations))) ? ';' : '';
		$count = 0;

		if (($this->options['compute_shorthand'] || !$this->options['allow_duplicate_declarations']) && $glue == ';') {

			$children = [];
			$properties = new PropertyList(null, $this->options);

			foreach ((isset($ast->children) ? $ast->children : []) as $child) {

				if (!empty($children)) {

					$children[] = $child;
				} else if ($child->type == 'Declaration' || $child->type == 'Comment') {

					$name = isset($child->name) ? $child->name : null;

					if (isset($name) && !$this->options['allow_duplicate_declarations'] && $properties->has($name)) {

						$properties->remove($name);
					}

					$properties->set($name, $child->value, $child->type, isset($child->leadingcomments) ? $child->leadingcomments : null, isset($child->trailingcomments) ? $child->trailingcomments : null, null, isset($child->vendor) ? $child->vendor : null);
				} else {

					$children[] = $child;
				}
			}

			if (!$properties->isEmpty()) {

				array_splice($children, 0, 0, iterator_to_array($properties->getProperties()));
			}

		} else {

			$children = isset($ast->children) ? $ast->children : [];
		}

		$result = [];
		settype($level, 'int');

		foreach ($children as $el) {

			if ($el instanceof RenderableInterface) {

				$el = $el->getAst();
			}

			$output = $this->{'render' . $el->type}($el, $level);

			if (trim($output) === '') {

				continue;

			} else if ($el->type != 'Comment') {

				if ($count == 0) {

					$count++;
				}
			}

			$output .= in_array($el->type, ['Declaration', 'Property']) ? ';' : '';

			if (isset($result[$output])) {

				unset($result[$output]);
			}

			$result[$output] = [$output, $el];
		}

		if ($this->options['remove_empty_nodes'] && $count == 0) {

			return '';
		}

		$join = $this->options['glue'];
		$output = '';

		foreach ($result as $res) {

			$output .= $res[0] . $join;
		}

		return rtrim($output, ';' . $this->options['glue']);
	}

	/**
	 * render a rule
	 * @param \stdClass $ast
	 * @param int|null $level
	 * @return string
	 * @throws Exception
	 * @ignore
	 */

	protected function renderNestingAtRule(\stdClass $ast, $level)
	{

		return $this->renderRule($ast, $level);
	}

	/**
	 * render a rule
	 * @param \stdClass $ast
	 * @param int|null $level
	 * @return string
	 * @throws Exception
	 * @ignore
	 */

	protected function renderNestingRule(\stdClass $ast, $level)
	{

		return $this->renderRule($ast, $level);
	}

	/**
	 * render a rule
	 * @param \stdClass $ast
	 * @param int|null $level
	 * @return string
	 * @throws Exception
	 * @ignore
	 */

	protected function renderNestingMediaRule(\stdClass $ast, $level)
	{

		return $this->renderAtRule($ast, $level);
	}

	/**
	 * @param \stdClass $ast
	 * @param int|null $level
	 * @return string
	 * @ignore
	 */
	protected function renderComment(\stdClass $ast, $level)
	{

		if ($this->options['remove_comments']) {

			if (!$this->options['preserve_license'] || !str_starts_with($ast->value, '/*!')) {

				return '';
			}
		}

		settype($level, 'int');

		if (!isset($this->indents[$level])) {

			$this->indents[$level] = str_repeat($this->options['indent'], $level);
		}

		return $this->indents[$level] . $ast->value;
	}

	/**
	 * render a rule
	 * @param \stdClass $ast
	 * @param int|null $level
	 * @return string
	 * @throws Exception
	 * @ignore
	 */

	protected function renderSelector(\stdClass $ast, $level)
	{

		$selector = $ast->selector;

		if (!isset($selector)) {

			throw new Exception('The selector cannot be empty', 400);
		}

		settype($level, 'int');

		if (!isset($this->indents[$level])) {

			$this->indents[$level] = str_repeat($this->options['indent'], $level);
		}

		$indent = $this->indents[$level];

		if (is_array($selector)) {

			if (empty($selector)) {

				// the selector is empty!
				throw new \Exception(sprintf('the selector is empty: %s:%s:%s', isset($ast->src) ? $ast->src : '', is_string($ast->position->line) ? $ast->position->line : '', isset($ast->position->column) ? $ast->position->column : ''), 400);
			}

			if (is_string($selector[0])) {

				$selector = implode(',' . $this->options['indent'], $selector);
			}
		}

		if (is_string($selector)) {

			$selector = Value::parse($selector, null, true, '', '');
		}

		$result = $indent . Value::renderTokens($selector, ['omit_unit' => false, 'compress' => $this->options['compress']], $this->options['glue'] . $indent);

		if ($ast->type == 'NestingAtRule' && !$this->options['legacy_rendering']) {

			$result = $indent . '@nest' . $this->options['indent'] . ltrim($result);
		}

		if (!$this->options['remove_comments'] && !empty($ast->leadingcomments)) {

			$comments = $ast->leadingcomments;

			$join = $this->options['compress'] ? '' : ' ';

			foreach ($comments as $comment) {

				$result .= $join . $comment;
			}
		}

		return $result;
	}

	/**
	 * @param \stdClass $ast
	 * @param int|null $level
	 * @return string
	 * @throws Exception
	 * @ignore
	 */

	protected function renderDeclaration($ast, $level)
	{

		return $this->renderProperty($ast, $level);
	}

	/**
	 * render a property
	 * @param \stdClass $ast
	 * @param int|null $level
	 * @return string
	 * @throws Exception
	 * @ignore
	 */

	protected function renderProperty(\stdClass $ast, $level)
	{
		if ($ast->type == 'Comment') {

			return empty($this->options['compress']) ? '' : $ast->value;
		}

		$name = $this->renderName($ast);

		if (class_exists(Value::getClassName($ast->name)) || !is_string($ast->value)) {

			$property = is_string($ast->value) ? Value::parse($ast->value, $ast->name) : $ast->value;
			$value = Value::renderTokens($property, array_merge(['property' => $name], $this->options));
		} else {

			$value = $ast->value;
		}

		if ($value == 'none') {

			if (in_array($name, ['border', 'border-top', 'border-right', 'border-left', 'border-bottom', 'outline'])) {

				$value = 0;
			}
		} else if (in_array($name, ['background', 'background-image', 'src'])) {

			$value = preg_replace_callback('#(^|\s)url\(\s*(["\']?)([^)\\2]+)\\2\)#', function ($matches) {

				if (str_contains($matches[3], 'data:')) {

					return $matches[0];
				}

				return $matches[1] . 'url(' . Helper::relativePath($matches[3], $this->outFile === '' ? Helper::getCurrentDirectory() : dirname($this->outFile)) . ')';
			}, $value);
		}

		if (!$this->options['remove_comments'] && !empty($ast->trailingcomments)) {

			foreach ($ast->trailingcomments as $comment) {

				$value .= ' ' . $comment;
			}
		}

		settype($level, 'int');

		if (!isset($this->indents[$level])) {

			$this->indents[$level] = str_repeat($this->options['indent'], $level);
		}

		return $this->indents[$level] . trim($name) . ':' . $this->options['indent'] . trim($value);
	}

	/**
	 * render a name
	 * @param \stdClass $ast
	 * @return string
	 * @ignore
	 */

	protected function renderName(\stdClass $ast)
	{

		$result = $ast->name;

		if (!empty($ast->vendor)) {

			$result = '-' . $ast->vendor . '-' . $result;
		}

		if (!$this->options['remove_comments'] && !empty($ast->leadingcomments)) {

			$comments = $ast->leadingcomments;

			foreach ($comments as $comment) {

				$result .= ' ' . $comment;
			}
		}

		return $result;
	}

	/**
	 * render a value
<<<<<<< HEAD
	 * @param \stdClass $ast
=======
	 * @param object $ast
>>>>>>> v.next
	 * @return string
	 * @throws Exception
	 * @ignore
	 */
	protected function renderValue(\stdClass $ast)
	{

		$result = Value::renderTokens(is_string($ast->value) ? Value::parse($ast->value, in_array($ast->type, ['Property', 'Declaration']) ? $ast->name : null, true, '', '') : $ast->value, $this->options);

		if (!$this->options['remove_comments'] && !empty($ast->trailingcomments)) {

			$trailingComments = $ast->trailingcomments;
		}

		if (!empty($trailingComments)) {

			$glue = $this->options['compress'] ? '' : ' ';

			foreach ($trailingComments as $comment) {

				$result .= $glue . $comment;
			}
		}

		return $result;
	}

	/**
	 * Set output formatting
	 * @param array $options
	 * @return Renderer
	 */
	public function setOptions(array $options)
	{

		if (!empty($options['compress'])) {

			$this->options['glue'] = '';
			$this->options['indent'] = '';

			if (!$this->options['convert_color']) {

				$this->options['convert_color'] = 'hex';
			}

			$this->options['charset'] = false;
			$this->options['remove_comments'] = true;
			$this->options['remove_empty_nodes'] = true;
		} else {

			$this->options['glue'] = "\n";
			$this->options['indent'] = ' ';
		}

		foreach ($options as $key => $value) {

			if (array_key_exists($key, $this->options)) {

				$this->options[$key] = $value;
			}
		}

		if ($this->options['convert_color'] === true) {

			$this->options['convert_color'] = 'hex';
		}

		if (isset($options['allow_duplicate_declarations'])) {

			$this->options['allow_duplicate_declarations'] = is_string($options['allow_duplicate_declarations']) ? [$options['allow_duplicate_declarations']] : $options['allow_duplicate_declarations'];
		}

		$this->indents = [];

		return $this;
	}

	/**
	 * return the options
	 * @param string|null $name
	 * @param mixed|null $default return value
	 * @return array
	 */
	public function getOptions($name = null, $default = null)
	{

		if (is_null($name)) {

			return $this->options;
		}

		return isset($this->options[$name]) ? $this->options[$name] : $default;
	}

	/**
	 * add sourcemap entry
	 * @param \stdClass $generated
	 * @param \stdClass $ast
	 * @return Renderer
	 * @ignore
	 */
	protected function addPosition(\stdClass $generated, \stdClass $ast)
	{

		if (empty($ast->src)) {

			return $this;
		}

		$position = isset($ast->location->start) ? $ast->location->start : (isset($ast->position) ? $ast->position : null);

		if (is_null($position)) {

			return $this;
		}

		$this->sourcemap->addPosition([
			'generated' => [
				'line' => $generated->line,
				'column' => $generated->column,
			],
			'source' => [
				'fileName' => $ast->src,
				'line' => $position->line - 1,
				'column' => $position->column - 1,
			],
		]);

		return $this;
	}

	/**
	 * @param \stdClass $position
	 * @param string $string
	 * @return object
	 * @ignore
	 */
	protected function update(\stdClass $position, $string)
	{

		$j = strlen($string);

		for ($i = 0; $i < $j; $i++) {

			if ($string[$i] == "\n") {

				$position->line++;
				$position->column = 0;
			} else {

				$position->column++;
			}
		}

		return $position;
	}

	/**
	 * flatten nested css tree
	 * @param \stdClass $node
	 * @return object
	 * @ignore
	 */
	protected function flattenChildren(\stdClass $node)
	{

		$node = clone $node;
		$children = array_map([$this, 'flatten'], $node->children);

		for ($i = 0; $i < count($children); $i++) {

			if ($children[$i]->type == 'Fragment') {

				array_splice($children, $i, 1, isset($children[$i]->children) ? $children[$i]->children : []);
				$i--;
			}
		}

		$node->children = $children;
		return $node;
	}

	/**
	 * @param \stdClass $node
	 * @return object
	 * @throws Exception
	 * @ignore
	 */
	public function flatten(\stdClass $node)
	{

		if (isset($node->children)) {

			switch ($node->type) {

				case 'AtRule':
				case 'NestingMediaRule':

					$node = $this->flattenChildren($node);

					$children = [];
					$frag = (object)[

						'type' => 'Fragment'
					];

					foreach ($node->children as $child) {

						if (in_array($child->type, ['NestingMediaRule', 'AtRule']) &&
							$child->name == 'media'
						) {

							if (!empty($children)) {

								$clone = clone $node;
								$clone->children = $children;
								$frag->children[] = $clone;
								$children = [];
							}

							$child = clone $child;

							$values = [];

							if (!empty($node->value)) {

								$value = Value::renderTokens(is_string($node->value) ? Value::parse($node->value, null, true, '', '', $node->name == 'charset') : $node->value, $this->options);

								if ($value !== '' && $value != 'all') {

									$values[$value] = $value;
								}
							}

							if (isset($child->value)) {

								$value = Value::renderTokens(is_string($child->value) ? Value::parse($child->value, null, true, '', '') : $child->value, $this->options);

								if ($value !== '' && $value != 'all') {

									$values[$value] = $value;
								}
							}

							if (!empty($values)) {

								$child->value = implode(' and ', $values);
							}

							$frag->children[] = $this->flatten($child);
							continue;
						}

						$children[] = $child;
					}

					if (!empty($children)) {

						$clone = clone $node;
						$clone->children = $children;
						$frag->children[] = $clone;
					}

					return $frag;

				case 'NestingRule':
				case 'NestingAtRule':

					$node = $this->flattenChildren($node);

					$children = [];
					$frag = (object)[

						'type' => 'Fragment'
					];

					if (is_object(isset($node->selector[0]) ? $node->selector[0] : null)) {

						$node->selector = Value::renderTokens($node->selector);
					}

					$selector = is_array($node->selector) ? $node->selector : Value::split($node->selector, ',');
					$selector = count($selector) > 1 ? ':is(' . implode(', ', array_map('trim', $selector)) . ')' : $selector[0];

					foreach ($node->children as $child) {

						if (in_array($child->type, ['Rule', 'NestingRule', 'NestingAtRule'])) {

							if (!empty($children)) {

								$clone = clone $node;
								$clone->children = $children;
								$children = [];
								$frag->children[] = $clone;
							}

							$child = clone $child;

							if (is_array($child->selector)) {

								$child->selector = array_map(function ($value) use ($selector) {

									return str_replace('&', $selector, $value);
								}, $child->selector);
							} else {

								$child->selector = str_replace('&', $selector, $child->selector);
							}

							$frag->children[] = $this->flatten($child);
							continue;
						}

						if (in_array($child->type, ['NestingMediaRule', 'AtRule']) &&
							$child->name == 'media'
						) {

							if (!empty($children)) {

								$clone = clone $node;
								$clone->children = $children;
								$children = [];
								$frag->children[] = $clone;
							}

							$clone = clone $node;
							$child = clone $child;

							$clone->children = isset($child->children) ? $child->children : [];
							$child->children = [$clone];
							$frag->children[] = $this->flatten($child);
							continue;
						}

						$children[] = $child;
					}

					if (!empty($children)) {

						$clone = clone $node;
						$clone->children = $children;
						$frag->children[] = $clone;
					}

					return $frag;

				case 'Stylesheet':

					return $this->flattenChildren($node);
			}
		}

		return $node;
	}
}