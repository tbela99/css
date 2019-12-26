<?php

namespace TBela\CSS;

use Exception;

/**
 * Pretty print CSS
 * @package CSS
 */
class Identity implements Renderer
{

    const MATCH_WORD = '/"(?:\\"|[^"])*"|\'(?:\\\'|[^\'])*\'/s';

    protected $indent = ' ';
    protected $glue = "\n";
    protected $separator = ' ';
    protected $charset = false;
    protected $rgba_hex = false;
    protected $remove_comments = false;
    protected $remove_empty_nodes = true;

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

        return $this;
    }

    /**
     * @param string|null $name
     * @return array
     */
    public function getOptions ($name = null) {

        $options = get_object_vars($this);

        unset($options['filter']);

        if (isset($options[$name])) {

            return $options[$name];
        }

        return $options;
    }



    /**
     * @param Element $element
     * @param null|int $level
     * @param bool $parent
     * @return string
     * @throws Exception
     */
    public function render(Element $element, $level = null, $parent = false)
    {

        if (!$this->shouldRender($element)) {

            return '';
        }

        if ($parent && !is_null($element['parent'])) {

            return $this->render($element->copy()->getRoot(), $level);
        }

        $indent = str_repeat($this->indent, (int) $level);

        switch ($element['type']) {

            case 'comment':
                return $this->remove_comments ? '' : (is_null($level) ? '' : str_repeat($this->indent, $level + 1)). $element->getValue();

            case 'stylesheet':

                return $this->renderCollection($element, $level);

            case 'declaration':

                return $indent . $this->indent . $this->renderDeclaration($element);

            case 'rule':

                return $this->renderRule($element, $level, $indent);

            case 'atrule':

                return $this->renderAtRule($element, $level, $indent);

            default:

                throw new Exception('Type not supported ' . $element->getType());
        }

        return '';
    }

    /**
     * @param ElementRule $element
     * @param int $level
     * @param string $indent
     * @return string
     * @throws Exception
     */
    protected function renderRule(ElementRule $element, $level, $indent)
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
     * @param ElementAtRule $element
     * @param int $level
     * @param string $indent
     * @return string
     * @throws Exception
     */
    protected function renderAtRule(ElementAtRule $element, $level, $indent)
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
     * @param ElementDeclaration $element
     * @return string
     */
    protected function renderDeclaration(ElementDeclaration $element)
    {

        return $this->renderName($element) . ':' . $this->indent . $this->renderValue($element);
    }

    protected function shouldRender(Element $element)
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
     * @param Element $element
     * @return string
     */
    protected function renderName(Element $element)
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

        $value = $element['value'];

        $hash = $this->escape($value);
        $value = $hash[0];

        $value = $this->filter->value($value, $element);

        if ($element['type'] == 'declaration') {

            $value = $this->filter->color($value, $element);
        }

        $value = $this->filter->whitespace($value);

        // remove unnecessary space
        return trim($this->unescape($value, $hash[1]));
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

        if ($type == 'rule' || ($type == 'atrule' && $element->hasDeclarations())) {

            $glue = ';';
        }

        $result = [];
        foreach ($element['children'] as $key => $el) {

            $output = $this->render($el, $level);

            if ($glue == ';' && trim($output) === '') {

                continue;
            }

            if ($el['type'] != 'comment') {

                $output .= $glue;
            }

            $result[] = $output;
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

        return implode($this->glue, $result);
    }
}


