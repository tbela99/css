<?php

namespace TBela\CSS;

use Exception;
use TBela\CSS\Element\Rule;
use TBela\CSS\Element\AtRule;
use TBela\CSS\Event\EventInterface;
use TBela\CSS\Event\EventTrait;
use TBela\CSS\Interfaces\RenderableInterface;
use TBela\CSS\Property\PropertyList;
use TBela\CSS\Value\Set;
use function is_string;

/**
 * Css node Renderer
 * @package TBela\CSS
 */
class Renderer implements EventInterface
{

    use EventTrait;

    const REMOVE_NODE = 1;

    /**
     * @var bool minify output
     * @ignore
     */
    protected $compress = false;

    /**
     * @var int CSS level 3|4
     * @ignore
     */
    protected $css_level = 4;

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
    protected $convert_color = false;

    /**
     * @var bool remove comments
     * @ignore
     */
    protected $remove_comments = false;

    /**
     * @var bool remove empty node
     * @ignore
     */
    protected $remove_empty_nodes = false;

    /**
     * @var bool|array|string true|false or a list of exceptions
     * @ignore
     */
    protected $allow_duplicate_declarations = false;

    /**
     * Identity constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {

        $this->setOptions($options);
        $this->on('traverse', function (RenderableInterface $node) {

            // remove comments
            if (($this->remove_comments && $node->getType() == 'Comment') ||
                // remove empty nodes
                ($this->remove_empty_nodes && !is_callable([$node, 'isLeaf']) && is_callable([$node, 'hasChildren']) && !$node->hasChildren())
            ) {

                return static::REMOVE_NODE;
            }
        });
    }

    /**
     * render an Element or a Property
     * @param RenderableInterface $element the element to render
     * @param null|int $level indention level
     * @param bool $parent render parent
     * @return string
     * @throws Exception
     */
    public function render(RenderableInterface $element, $level = null, $parent = false)
    {

        foreach ($this->emit('traverse', $element, $level) as $result) {

            if ($result === static::REMOVE_NODE) {

                return '';
            }

            if (is_string($result)) {

                return $result;
            }

            if ($result instanceof RenderableInterface) {

                $element = $result;
                break;
            }
        }

        if ($parent && ($element instanceof Element) && !is_null($element['parent'])) {

            return $this->render($element->copy()->getRoot(), $level);
        }

        $indent = str_repeat($this->indent, (int)$level);

        switch ($element['type']) {

            case 'Comment':

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

        $selector = $element->getSelector();

        if (empty($selector)) {

            throw new Exception('The selector cannot be empty');
        }

        $output = $this->renderCollection($element, is_null($level) ? 0 : $level + 1);

        if ($output === '' && $this->remove_empty_nodes) {

            return '';
        }

        $result = $indent . implode(',' . $this->glue . $indent, $selector);

        if (!$this->remove_comments) {

            $comments = $element->getLeadingComments();

            if (!empty($comments)) {

                $result .= ($this->compress ? '' : ' ').implode(' ', $comments);
            }
        }

        return $result . $this->indent . '{' .
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

        if ($value !== '') {

            if ($this->compress && $value[0] == '(') {

                $output .= $value;
            }

            else {

                $output .= rtrim($this->separator . $value);
            }
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
     * @param RenderableInterface $element
     * @return string
     * @ignore
     */

    protected function renderProperty(RenderableInterface $element)
    {
        $name = $this->renderName($element);
        $value = $element->getValue();

        $options = [
            'compress' => $this->compress,
            'css_level' => $this->css_level,
            'convert_color' => $this->convert_color === true ? 'hex' : $this->convert_color];

        if (empty($this->compress)) {

            $value = implode(', ',  array_map(function (Set $value) use($options) {

                return $value->render($options);

            }, $value->split(',')));
        }

        else {

            $value = $value->render($options);
        }

        //->render();

        if ($value == 'none' && in_array($name, ['border', 'border-top', 'border-right', 'border-left', 'border-bottom', 'outline'])) {

            $value = 0;
        }

        if(!$this->remove_comments) {

            $comments = $element->getTrailingComments();

            if (!empty($comments)) {

                $value .= ' '.implode(' ', $comments);
            }
        }

        return trim($name).':'.$this->indent.trim($value);
    }

    /**
     * render a name
     * @param RenderableInterface $element
     * @return string
     * @ignore
     */
    protected function renderName(RenderableInterface $element)
    {

        $result = $element->getName();

        if (!$this->remove_comments) {

            $comments = $element->getLeadingComments();

            if (!empty($comments)) {

                $result.= ' '.implode(' ', $comments);
            }
        }

        return $result;
    }

    /**
     * render a value
     * @param Element $element
     * @return string
     * @return string
     * @ignore
     */
    protected function renderValue(Element $element)
    {
        $result = $element->getValue();

        if (!($result instanceof Set)) {

        $value = $element->getValue();

        if (is_string($value)) {

            $value = Value::parse($value);
        }

        return trim($value->render($this->getOptions()));
    }

        $result = $result->render($this->getOptions());

        if (!$this->remove_comments) {

            $trailingComments = $element['trailingcomments'];

            if (!empty($trailingComments)) {

                $result .= ($this->compress ? '' : ' ').implode(' ', $trailingComments);
            }
        }

        return $result;
    }

    /**
     * render a list
     * @param RuleList $element
     * @param int|null $level
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

                    continue;

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

        // remove identical rules
        while ($i--) {

            if (!isset($hash[$result[$i]])) {

                $hash[$result[$i]] = 1;
            } else {

                array_splice($result, $i, 1);
            }
        }

        return rtrim(implode($this->glue, $result), $glue . $this->glue);
    }
    /**
     * Set output formatting
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {

        if (isset($options['compress'])) {

            $this->compress = $options['compress'];

            if ($this->compress) {

                $this->glue = '';
                $this->indent = '';
                $this->convert_color = 'hex';
                $this->charset = false;
                $this->remove_comments = true;
                $this->remove_empty_nodes = true;
            } else {

                $this->glue = "\n";
                $this->indent = ' ';
            }
        }

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

        if (isset($options['convert_color'])) {

            $this->convert_color = $options['convert_color'];
        }

        if (isset($options['allow_duplicate_declarations'])) {

            $this->allow_duplicate_declarations = is_string($options['allow_duplicate_declarations']) ? [$options['allow_duplicate_declarations']] : $options['allow_duplicate_declarations'];
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

        $options = array_filter(get_object_vars($this), function ($property) {
            return !is_object($property);
        });

        if (isset($options[$name])) {

            return $options[$name];
        }

        if (!is_null($name)) {

            return $default;
        }

        return $options;
    }
}
