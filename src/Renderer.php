<?php

namespace TBela\CSS;

use Exception;
use TBela\CSS\Element\Rule;
use TBela\CSS\Element\AtRule;

// use TBela\CSS\Element\Comment;
use TBela\CSS\Element\Declaration;
use TBela\CSS\Property\PropertyList;
use TBela\CSS\Property\RenderableProperty;
use TBela\CSS\Value\Set;
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

    /**
     * @var bool minify output
     * @ignore
     */
    protected $compress = false;

    /**
     * @var int CSS level 3|4
     * @ignore
     */
    protected $css_level = 3;

    /**
     * @var string line indention
     * @ignore
     */
    protected $indent = ' ';

    /**
     * @var string line separator
     * @ignore
     */
    protected $glue = "\n";

    /**
     * @var string token separator
     * @ignore
     */
    protected $separator = ' ';

    /**
     * @var bool preserve charset
     * @ignore
     */
    protected $charset = false;

    /**
     * @var bool allow rbga hex color
     * @ignore
     */
    protected $rgba_hex = false;

    /**
     * @var bool remove comments
     * @ignore
     */
    protected $remove_comments = false;

    /**
     * @var bool remove empty node
     * @ignore
     */
    protected $remove_empty_nodes = true;

    /**
     * @var bool|array|string true|false or a list of exceptions
     * @ignore
     */
    protected $allow_duplicate_declarations = true;

    /**
     * Identity constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {

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
            } else {

                $this->glue = "\n";
                $this->indent = ' ';
            }
        }

        return $this;
    }

    /**
     * return the options
     * @param string|null $name
     * @param mixed $default return value
     * @return array
     */
    public function getOptions($name = null, $default = null)
    {

        $options = get_object_vars($this);

        if (isset($options[$name])) {

            return $options[$name];
        }

        if (!is_null($name)) {

            return $default;
        }

        return array_filter(get_object_vars($this), function ($property) {
            return !is_object($property);
        });
    }

    /**
     * render an Element or a Property
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

        $indent = str_repeat($this->indent, (int)$level);

        switch ($element['type']) {

            case 'Comment':

                if ($this->remove_comments) {

                    return '';
                }

                return (is_null($level) ? '' : str_repeat($this->indent, $level + 1)) . $element['value'];

            case 'Stylesheet':

                return $this->renderCollection($element, $level);

            case 'Declaration':
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
     * render a rule
     * @param Rule $element
     * @param int $level
     * @param string $indent
     * @return string
     * @throws Exception
     * @ignore
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
     * render at-rule
     * @param AtRule $element
     * @param int $level
     * @param string $indent
     * @return string
     * @throws Exception
     * @ignore
     */
    protected function renderAtRule(AtRule $element, $level, $indent)
    {

        if ($element['name'] == 'charset' && !$this->charset) {

            return '';
        }

        $output = '@' . $this->renderName($element);

        $value = $this->renderValue($element);

        if ($output == '@import') {

            // rewrite atrule @import url(https://foobar) -> @import https://foobar
            $value = preg_replace_callback('#url\(\s*(["\']?)(.*?)\1\)#s', function ($matches) {

                return trim($matches[2]);
            }, $value);
        }

        if ($value !== '') {

            $output .= $this->separator . $value;
        }

        if ($element->isLeaf()) {

            return $indent . $output . ';';
        }

        $elements = $this->renderCollection($element, $level + 1);

        if ($elements === '' && $this->remove_empty_nodes) {

            return '';
        }

        return $indent . $output . $this->indent . '{' . $this->glue . $elements . $this->glue . $indent . '}';
    }

    /**
     * render a property
     * @param RenderableProperty $element
     * @return string
     * @ignore
     */

    protected function renderProperty(RenderableProperty $element)
    {

        $propertyList = new PropertyList(null, array_merge($this->getOptions(), ['allow_duplicate_declarations' => false]));

        $propertyList->set($element['name'], $element['value']);

        $result = '';

        foreach ($propertyList->getProperties() as $property) {

            $result .= $property['name'].':'.$this->indent.$this->filterValue($property['name'], $property['value']);
            break;
        }

        return $result;
    }

    protected function filterValue($name, Set $values) {

        $options = $this->getOptions();

        if ($this->compress) {

            $result = '';
            $name = (string) $name;

            $array_values = $values->toArray();

            foreach ($array_values as $value) {

                switch ($name) {

                    case 'border':
                    case 'border-top':
                    case 'border-right':
                    case 'border-left':
                    case 'border-bottom':
                    case 'outline':

                        if (trim($value) == 'none') {

                            $result .= '0';
                        }
                        else {

                            $result .= $value->render($options, $name);
                        }

                        break;

                    default:

                        $result .= $value->render($options, $name);
                        break;
                }
            }

            return $result;
        }

        return $values->render($options);
    }

    /**
     * test if an item should be rendered
     * @param Rendererable $element
     * @return bool
     * @ignore
     */
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
     * render a name
     * @param Rendererable $element
     * @return string
     * @ignore
     */
    protected function renderName(Rendererable $element)
    {

        return $element->getName();
    }

    /**
     * render a value
     * @param Element $element
     * @return string
     * @ignore
     */
    protected function renderValue(Element $element)
    {

        return trim(Value::parse($element->getValue())->render($this->getOptions()));
    }

    /**
     * render a selector
     * @param array $selector
     * @param string $indent
     * @return string
     * @ignore
     */
    protected function renderSelector(array $selector, $indent)
    {

        return $indent . implode(',' . $this->glue . $indent, $selector);
    }

    /**
     * render a list
     * @param RuleList $element
     * @param int $level
     * @return string
     * @throws Exception
     * @ignore
     */
    protected function renderCollection(RuleList $element, $level)
    {

        $glue = '';
        $type = $element['type'];
        $count = 0;

        if ($type == 'Rule' || ($type == 'AtRule' && $element->hasDeclarations())) {

            $glue = ';';

            $children = new PropertyList($element, $this->getOptions());
        } else {

            $children = $element['children'];
        }

        $result = [];

        foreach ($children as $el) {

            $output = $this->render($el, $level);

            if (trim($output) === '') {

                if ($glue == ';') {

                    continue;
                }
            } else if ($el['type'] != 'Comment') {

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
            } else {

                array_splice($result, $i, 1);
            }
        }

        return rtrim(implode($this->glue, $result), $glue . $this->glue);
    }
}
