<?php 

namespace TBela\CSS;

use Exception;
use TBela\CSS\Element\Rule;
use TBela\CSS\Element\AtRule;
// use TBela\CSS\Element\Comment;
use TBela\CSS\Element\Declaration;
use TBela\CSS\Property\PropertyList;
use TBela\CSS\Property\RenderableProperty;
use function is_string;

/**
 * Css node Renderer
 * @package TBela\CSS
 */
class Renderer
{

    /**
     * @ignore
     */
    const MATCH_WORD = '/"(?:\\"|[^"])*"|\'(?:\\\'|[^\'])*\'/s';

    /** @var bool minify output */
    protected $compress = false;

    /**
     * @var int CSS level 3|4
     */
    protected $css_level = 3;

    /** @var string line indention */
    protected $indent = ' ';

    /** @var string line separator */
    protected $glue = "\n";

    /** @var string token separator */
    protected $separator = ' ';

    /** @var bool preserve charset */
    protected $charset = false;

    /** @var bool allow rbga hex color */
    protected $rgba_hex = false;

    /** @var bool remove comments */
    protected $remove_comments = false;

    /** @var bool remove empty node */
    protected $remove_empty_nodes = true;

    /**
     * @var bool|array|string true|false or a list of exceptions
     */
    protected $allow_duplicate_declarations = true;

    /**
     * @var Filter
     */
    protected $filter = null;

    /**
     * Identity constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {

        $this->filter = new Filter($this);
        $this->setOptions($options);
    }

    /**
     * Set output formatting
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {

        if (isset($options['indent'])) {

            $this->indent = $options['indent'];
        }

        if (isset($options['charset'])) {

            $this->charset = $options['charset'];
        }

        if (isset($options['css_level'])) {

            $this->css_level = $options['css_level'];
        }

        if (isset($options['glue'])) {

            $this->glue = $options['glue'];
        }

        if (isset($options['remove_empty_nodes'])) {

            $this->remove_empty_nodes = $options['remove_empty_nodes'];
        }

        if (isset($options['remove_comments'])) {

            $this->remove_comments = $options['remove_comments'];
        }

        if (isset($options['rgba_hex'])) {

            $this->rgba_hex = $options['rgba_hex'];
        }

        if (isset($options['allow_duplicate_declarations'])) {

            $this->allow_duplicate_declarations = is_string($options['allow_duplicate_declarations']) ? [$options['allow_duplicate_declarations']] : $options['allow_duplicate_declarations'];
        }

        if (isset($options['compress'])) {

            $this->compress = $options['compress'];

            if ($this->compress) {

                $this->glue = '';
                $this->indent = '';
                $this->charset = false;
                $this->remove_comments = true;
                $this->remove_empty_nodes = true;
            }

            else {

                $this->glue = "\n";
                $this->indent = ' ';
            }
        }

        return $this;
    }

    /**
     * @param string|null $name
     * @param mixed $default return value
     * @return array
     */
    public function getOptions ($name = null, $default = null) {

        $options = get_object_vars($this);

        unset($options['filter']);

        if (isset($options[$name])) {

            return $options[$name];
        }

        if (!is_null($name)) {

            return $default;
        }

        return array_filter(get_object_vars($this), function ($property) { return !is_object($property); });
    }

    /**
     * @param Rendererable $element the element to render
     * @param null|int $level indention level
     * @param bool $parent render parent
     * @return string
     * @throws Exception
     */
    public function render(Rendererable $element, $level = null, $parent = false)
    {

        if (!$this->shouldRender($element)) {

            return '';
        }

        if ($parent && ($element instanceof Element) && !is_null($element['parent'])) {

            return $this->render($element->copy()->getRoot(), $level);
        }

        $indent = str_repeat($this->indent, (int) $level);

        switch ($element['type']) {

            case 'Comment':

                if ($this->remove_comments) {

                    return  '';
                }

                return (is_null($level) ? '' : str_repeat($this->indent, $level + 1)). $element['value'];

            case 'Stylesheet':

                return $this->renderCollection($element, $level);

            case 'Declaration':

                return $indent . $this->indent . $this->renderDeclaration($element);

            case 'Property':

                return $indent . $this->indent . $this->renderProperty($element);

            case 'Rule':

                return $this->renderRule($element, $level, $indent);

            case 'AtRule':

                return $this->renderAtRule($element, $level, $indent);

            default:

                throw new Exception('Type not supported ' . $element->getType());
        }

        return '';
    }

    /**
     * @param Rule $element
     * @param int $level
     * @param string $indent
     * @return string
     * @throws Exception
     */
    protected function renderRule(Rule $element, $level, $indent)
    {

        if (empty($element['selector'])) {

            throw new Exception('The selector cannot be empty');
        }

        $output = $this->renderCollection($element, is_null($level) ? 0 : $level + 1);

        if ($output === '' && $this->remove_empty_nodes) {

            return '';
        }

        return $this->renderSelector($element['selector'], $indent) . $this->indent . '{' .
            $this->glue .
            $output . $this->glue .
            $indent .
            '}';
    }

    /**
     * @param AtRule $element
     * @param int $level
     * @param string $indent
     * @return string
     * @throws Exception
     */
    protected function renderAtRule(AtRule $element, $level, $indent)
    {

        if ($element['name'] == 'charset' && !$this->charset) {

            return '';
        }

        $output = $indent . '@' . $this->renderName($element);

        $value = $this->renderValue($element);

        if ($value !== '') {

            $output .= $this->separator . $value;
        }

        if ($element->isLeaf()) {

            return $output . ';';
        }

        $elements = $this->renderCollection($element, $level + 1);

        if ($elements === '' && $this->remove_empty_nodes) {

            return '';
        }

        return $output . $this->indent . '{' . $this->glue . $elements . $this->glue . $indent . '}';
    }

    /**
     * @param Declaration $element
     * @return string
     */

    protected function renderDeclaration(Declaration $element)
    {

        $property = new PropertyList(null, array_merge($this->getOptions(), ['allow_duplicate_declarations' => false]));
        $property->set($element['name'], $element['value']);

        return (string) $property;
    }

    /**
     * @param RenderableProperty $element
     * @return string
     */

    protected function renderProperty(RenderableProperty $element)
    {

        return $this->renderName($element) . ':' . $this->indent . $this->filter->value($element['value']->render($this->getOptions()), $element);
    }

    protected function shouldRender(Rendererable $element)
    {

        if (is_callable([$element, 'hasChildren'])) {

            if (is_callable([$element, 'isLeaf']) && $element->isLeaf()) {

                return true;
            }

            return $element->hasChildren() || !$this->remove_empty_nodes;
        }

        return true;
    }

    /**
     * @param Rendererable $element
     * @return string
     */
    protected function renderName(Rendererable $element)
    {

        return $element['name'];
    }

    protected function escape($value)
    {

        $replace = [];

        $value = preg_replace_callback(static::MATCH_WORD, function ($matches) use (&$replace) {

            if (empty($matches[1])) {

                return $matches[0];
            }

            $replace[$matches[1]] = '~~' . crc32($matches[1]) . '~~';

            return str_replace($matches[1], $replace[$matches[1]], $matches[0]);

        }, $value);

        return [$value, $replace];
    }

    protected function unescape($value, $replace)
    {

        if (empty($replace)) {

            return $value;
        }

        return str_replace(array_values($replace), array_keys($replace), $value);
    }

    /**
     * @param Element $element
     * @return string
     */
    protected function renderValue(Element $element)
    {

        return trim(Value::parse($element['value'])->render($this->getOptions()));
    }

    /**
     * @param array $selector
     * @param string $indent
     * @return string
     */
    protected function renderSelector(array $selector, $indent)
    {

        return $indent . implode(',' . $this->glue.$indent, $selector);
    }

    /**
     * @param RuleList $element
     * @param int $level
     * @return string
     * @throws Exception
     */
    protected function renderCollection(RuleList $element, $level)
    {

        $glue = '';
        $type = $element['type'];
        $count = 0;

        if ($type == 'Rule' || ($type == 'AtRule' && $element->hasDeclarations())) {

            $glue = ';';

            $children = new PropertyList($element, $this->getOptions());
        }

        else {

            $children = $element['children'];
        }

        $result = [];

        foreach ($children as $el) {

            $output = $this->render($el, $level);

            if (trim($output) === '') {

                if ($glue == ';') {

                    continue;
                }
            }

            else if ($el['type'] != 'Comment') {

                if ($count == 0) {

                    $count++;
                }
            }

            if ($el['type'] != 'Comment') {

                $output .= $glue;
            }

            $result[] = $output;
        }

        if ($this->remove_empty_nodes && $count == 0) {

            return '';
        }

        $hash = [];

        $i = count($result);

        while ($i--) {

            if (!isset($hash[$result[$i]])) {

                $hash[$result[$i]] = 1;
            }

            else {

                array_splice($result, $i, 1);
            }
        }

        return rtrim(implode($this->glue, $result), $glue.$this->glue);
    }
}
