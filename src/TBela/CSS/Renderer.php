<?php

namespace TBela\CSS;

use Exception;
use TBela\CSS\Element\Rule;
use TBela\CSS\Element\AtRule;
use TBela\CSS\Interfaces\RenderableInterface;
use TBela\CSS\Interfaces\ElementInterface;
use TBela\CSS\Property\PropertyList;
use TBela\CSS\Value\Set;
use function is_string;

/**
 * Css node Renderer
 * @package TBela\CSS
 */
class Renderer
{
    /**
     * @var Traverser
     */
    protected $traverser = null;

    protected array $options = [
        'compress' => false,
        'css_level' => 4,
        'indent' => ' ',
        'glue' => "\n",
        'separator' => ' ',
        'charset' => false,
        'convert_color' => false,
        'remove_comments' => false,
        'compute_shorthand' => true,
        'remove_empty_nodes' => false,
        'allow_duplicate_declarations' => false
    ];

    protected array $indents = [];
    protected array $events = [];

    /**
     * Identity constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {

        $this->setOptions($options);
    }

    /**
     * render an ElementInterface or a Property
     * @param RenderableInterface $element the element to render
     * @param null|int $level indention level
     * @param bool $parent render parent
     * @return string
     * @throws Exception
     */
    public function render(RenderableInterface $element, ?int $level = null, $parent = false)
    {

        if ($parent && ($element instanceof ElementInterface) && !is_null($element['parent'])) {

            return $this->render($element->copy()->getRoot(), $level);
        }

        if (isset($this->traverser)) {

            $result = $this->traverser->traverse($element);

            if ($result instanceof ElementInterface) {

                $element = $result;
            }
        }

        $type = $element->getType();

        switch ($type) {

            case 'Stylesheet':

                return $this->renderCollection($element, $level);

            case 'Comment':
            case 'Declaration':
            case 'Property':
            case 'Rule':
            case 'AtRule':

                return $this->{'render'.$type}($element, $level);

            default:

                throw new Exception('Type not supported ' . $type);
        }

        return '';
    }

    /**
     * @param RenderableInterface $element
     * @param int|null $level
     * @return string
     */
    protected function renderComment(RenderableInterface $element, ?int $level) {

        if ($this->options['remove_comments']) {

            return '';
        }

        settype($level, 'int');

        if (!isset($this->indents[$level])) {

            $this->indents[$level] = str_repeat($this->options['indent'], $level);
        }

        return $this->indents[$level] . $element['value'];
    }

    /**
     * render a rule
     * @param Rule $element
     * @param int|null $level
     * @return string
     * @throws Exception
     * @ignore
     */
    protected function renderRule(Rule $element, $level)
    {

        $selector = $element->getSelector();

        if (empty($selector)) {

            throw new Exception('The selector cannot be empty');
        }

        $output = $this->renderCollection($element, $level + 1);

        if ($output === '' && $this->options['remove_empty_nodes']) {

            return '';
        }

        settype($level, 'int');

        if (!isset($this->indents[$level])) {

            $this->indents[$level] = str_repeat($this->options['indent'], $level);
        }

        $indent = $this->indents[$level];

        $result = $indent;

        $join = ',' . $this->options['glue'] . $indent;

        foreach ($selector as $sel) {

            $result .= $sel.$join;
        }

        $result = rtrim($result, $join);

        if (!$this->options['remove_comments']) {

            $comments = $element->getLeadingComments();

            if (!empty($comments)) {

                $join = $this->options['compress'] ? '' : ' ';

                foreach ($comments as $comment) {

                    $result .= $join.$comment;
                }
            }
        }

        return $result . $this->options['indent'] . '{' .
            $this->options['glue'] .
            $output . $this->options['glue'] .
            $indent .
        '}';
    }

    /**
     * render at-rule
     * @param AtRule $element
     * @param ?int $level
     * @return string
     * @ignore
     */
    protected function renderAtRule(AtRule $element, $level)
    {

        if ($element['name'] == 'charset' && !$this->options['charset']) {

            return '';
        }

        $output = '@' . $this->renderName($element);
            $value = $this->renderValue($element);

        if ($value !== '') {

            if ($this->options['compress'] && $value[0] == '(') {

                $output .= $value;
            }

            else {

                $output .= rtrim($this->options['separator'] . $value);
            }
        }

        settype($level, 'int');

        if (!isset($this->indents[$level])) {

            $this->indents[$level] = str_repeat($this->options['indent'], $level);
        }

        $indent = $this->indents[$level];

        if ($element->isLeaf()) {

            return $indent . $output . ';';
        }

        $elements = $this->renderCollection($element, $level + 1);

        if ($elements === '' && $this->options['remove_empty_nodes']) {

            return '';
        }

        return $indent . $output . $this->options['indent'] . '{' . $this->options['glue'] . $elements . $this->options['glue'] . $indent . '}';
    }

    protected function renderDeclaration(RenderableInterface $element, ?int $level) {

        return $this->renderProperty($element, $level);
    }
    /**
     * render a property
     * @param RenderableInterface $element
     * @return string
     * @ignore
     */

    protected function renderProperty(RenderableInterface $element, ?int $level)
    {
        $name = $this->renderName($element);
        $value = $element->getValue();

        $options = [
            'compress' => $this->options['compress'],
            'css_level' => $this->options['css_level'],
            'convert_color' => $this->options['convert_color'] === true ? 'hex' : $this->options['convert_color']];

        if (empty($this->options['compress'])) {

            $value = implode(', ',  array_map(function (Set $value) use($options) {

                return $value->render($options);

            }, $value->split(',')));
        }

        else {

            $value = $value->render($options);
        }

        if ($value == 'none' && in_array($name, ['border', 'border-top', 'border-right', 'border-left', 'border-bottom', 'outline'])) {

            $value = 0;
        }

        if(!$this->options['remove_comments']) {

            $comments = $element->getTrailingComments();

            if (!empty($comments)) {

                foreach ($comments as $comment) {

                    $value .= ' '.$comment;
                }
            }
        }

        settype($level, 'int');

        if (!isset($this->indents[$level])) {

            $this->indents[$level] = str_repeat($this->options['indent'], $level);
        }

        return $this->indents[$level].trim($name).':'.$this->options['indent'].trim($value);
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

        if (!$this->options['remove_comments']) {

            $comments = $element->getLeadingComments();

            if (!empty($comments)) {

                foreach ($comments as $comment) {

                    $result .= ' '.$comment;
                }
            }
        }

        return $result;
    }

    /**
     * render a value
     * @param ElementInterface $element
     * @return string
     * @return string
     * @ignore
     */
    protected function renderValue(ElementInterface $element)
    {
        $result = $element->getValue();

        if (!($result instanceof Set)) {

            $result = Value::parse($result, $element['name']);
            $element->setValue($result);
        }

        $result = $result->render($this->options);

        if (!$this->options['remove_comments']) {

            $trailingComments = $element['trailingcomments'];

        }

        if (!empty($trailingComments)) {

            $glue = $this->options['compress'] ? '' : ' ';

            foreach ($trailingComments as $comment) {

                $result .= $glue.$comment;
            }
        }

        return $result;
    }

    /**
     * render a list
     * @param RuleList $element
     * @param int|null $level
     * @return string
     * @ignore
     */
    protected function renderCollection(RuleList $element, ?int $level)
    {

        $glue = '';
        $type = $element->getType();
        $count = 0;

        if (($this->options['compute_shorthand'] || !$this->options['allow_duplicate_declarations']) && ($type == 'Rule' || ($type == 'AtRule' && $element->hasDeclarations()))) {

            $glue = ';';
            $children = new PropertyList($element, $this->options);
        } else {

            $children = $element->getChildren();
        }

        $result = [];

        settype($level, 'int');

        foreach ($children as $el) {

            $output = $this->{'render'.$el->getType()}($el, $level);

            if (trim($output) === '') {

                    continue;

            } else if ($el->getType() != 'Comment') {

                if ($count == 0) {

                    $count++;
                }
            }

            if ($el->getType() != 'Comment') {

                $output .= $glue;
            }

            if (isset($result[$output])) {

                unset($result[$output]);
            }

            $result[$output] = $output;
        }

        if ($this->options['remove_empty_nodes'] && $count == 0) {

            return '';
        }

        $join = $this->options['glue'];
        $output = '';

        foreach ($result as $res) {

            $output .= $res.$join;
        }

        return rtrim($output, $glue . $this->options['glue']);
    }
    /**
     * Set output formatting
     * @param array $options
     * @return $this
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
     * @param mixed $default return value
     * @return array
     */
    public function getOptions($name = null, $default = null)
    {

        if (is_null($name)) {

            return $this->options;
        }

        return $this->options[$name] ?? $default;
    }

    public function on($type, $callable) {

        if (is_null($this->traverser)) {

            $this->traverser = new Traverser();
        }

        $this->traverser->on($type == 'traverse' ? 'enter' : $type, $callable);

        return $this;
    }

    public function off($type, $callable) {

        if (isset($this->traverser)) {

            $this->traverser->off($type == 'traverse' ? 'enter' : 'traverse', $callable);
        }

        return $this;
    }
}
