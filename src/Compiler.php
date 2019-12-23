<?php 

namespace TBela\CSS;

use Exception;

/**
 * Expose `Compiler`.
 */

class Compiler {

    protected $options = [
	    'indent' => ' ',
        'glue' => "\n",
        'separator' => ' ',
        'charset' => false,
        'rgba_hex' => false,
        'compress' => false,
        'remove_comments' => false,
        'remove_empty_nodes' => false
    ];

    /**
     * @var Element
     */
	protected $data;

    /**
     * Compiler constructor.
     * @param array $options
     */
	public function __construct (array $options = []) {

		$this->setOptions($options);
	}

	public function setOptions (array $options) {

	    foreach (array_keys($this->options) as $key) {

	        if (isset($options[$key])) {

	            $this->options[$key] = $options[$key];
            }
        }

	    return $this;
    }

    /**
     * @param string $css
     * @return Compiler
     * @throws Exception
     */
	public function setContent ($css) {

	    $this->data = Element::getInstance((new Parser($css, $this->options))->parse());
	    return $this;
    }

    /**
     * @param object $ast
     * @return Compiler
     */
	public function setData ($ast) {

		$this->data = Element::getInstance($ast, $this->options);
		return $this;
	}

    /**
     * @return string
     * @throws Exception
     */
	public function compile () {

		if (isset($this->data)) {

		    $renderer = !empty($this->options['compress']) ? new Compress($this->options) : new Identity($this->options);

			return $renderer->render($this->data);
		}

		return '';
	}

    /**
     * @return Element
     */
	public function getData() {

	    return $this->data;
    }
}
