<?php 

namespace TBela\CSS;

/**
 * Pretty print CSS
 * @package CSS
 */
class Identity implements Renderer {

	protected $indent = ' ';
	protected $glue = "\n";
	protected $separator = ' ';
	protected $charset = false;
	protected $rgba_hex = false;
    protected $remove_comments = false;
	protected $remove_empty_nodes = false;

    /**
     * Identity constructor.
     * @param array $options
     */
	public function __construct(array $options) {

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
	}

    /**
     * @param Element $element
     * @param null|int $level
     * @return string
     * @throws \Exception
     */
	public function render (Element $element, $level = null) {

	    if (!$this->shouldRender($element)) {

	        return '';
        }

		$indent = str_repeat($this->indent, (int) $level);

		switch ($element->getType()) {

			case 'comment':
				return $this->remove_comments ? '' : $element->getValue();

			case 'stylesheet':

				return $this->renderCollection($element,  $level);

            case 'declaration':

                return $indent.$this->indent.$this->renderDeclaration($element);

            case 'rule':

                return $this->renderRule($element, $level, $indent);

            case 'atrule':

                return $this->renderAtRule($element, $level, $indent);

			default:

				throw new \Exception('Type not supported '.$element->getType());
		}

		return '';
	}

    /**
     * @param ElementRule $element
     * @param int $level
     * @param string $indent
     * @return string
     * @throws \Exception
     */
	protected function renderRule(ElementRule $element, $level, $indent) {

        $output = $this->renderCollection($element, is_null($level) ? 0 : $level + 1);

        if ($output === '' && $this->remove_empty_nodes) {

            return '';
        }

        return $indent.$this->renderSelector($element->getSelector(), (int) $level).$this->indent.'{'.
                $this->glue.
                $output.$this->glue.
                $indent.
            '}';
    }

    /**
     * @param ElementAtRule $element
     * @param int $level
     * @param string $indent
     * @return string
     * @throws \Exception
     */
    protected function renderAtRule(ElementAtRule $element, $level, $indent) {

        if ($element->getName() == 'charset' && !$this->charset) {

            return '';
        }

        $output = $indent.'@'.$this->renderName($element->getName());

        $value = $this->renderValue($element->getValue(), $element->getType());

        if ($value !== '') {

            $output .= $this->separator.$value;
        }

        if ($element->isLeaf()) {

            return $output.';';
        }

        $elements = $this->renderCollection($element, $level + 1);

        if ($elements === '' && $this->remove_empty_nodes) {

            return '';
        }

        return $output.$this->indent.'{'.$this->glue.$elements.$this->glue.$indent.'}';
    }

    /**
     * @param ElementDeclaration $element
     * @return string
     */
	protected function renderDeclaration (ElementDeclaration $element) {

	    $name = $element->getName();

	    return $this->renderName($name).':'.$this->indent.$this->renderValue($element->getValue(), $element->getType());
    }

	protected function shouldRender (Element $element) {

	    if (is_callable([$element, 'hasChildren'])) {

	        if (is_callable([$element, 'isLeaf']) && $element->isLeaf ()) {

	            return true;
            }

	        return $element->hasChildren() || !$this->remove_empty_nodes;
        }

	    return true;
    }

    /**
     * @param string $name
     * @return string
     */
	protected function renderName ($name) {

	    return $name;
    }

    /**
     * @param string $value
     * @param string $type
     * @return string
     */
    protected function renderValue ($value, $type = null) {

        return $value;
    }

    /**
     * @param array $selector
     * @param integer $level
     * @return string
     */
	protected function renderSelector (array $selector, $level) {

	    return str_repeat($this->indent, $level).implode(','.$this->glue, $selector);
    }

    /**
     * @param Elements $element
     * @param int $level
     * @return string
     * @throws \Exception
     */
	protected function renderCollection (Elements $element, $level) {

	    $glue = '';
	    $type = $element->getType();

	    if ($type == 'rule' || ($type == 'atrule' && $element->hasDeclarations ())) {

	        $glue = ';';
        }

	    $result = '';
	    $lastType = '';

	    foreach ($element as $el) {

	        $output = $this->render($el, $level);

	        if ($glue == ';' && trim($output) === '') {

                $lastType = $el->getType();
	            continue;
            }

	        if ($result === '') {

	            $result = $output;
	            $lastType = $el->getType();
                continue;
            }

	        $result .= ($el->getType() == 'comment' || $lastType == 'comment' ? '' : $glue).$this->glue.$output;
            $lastType = $el->getType();
        }

	    return $result;
	}
}


