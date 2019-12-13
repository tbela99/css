<?php 

namespace TBela\CSS;

/**
 * Expose `Compiler`.
 */

class Compiler {

	protected $options = [];
	protected $data;

    /**
     * Compiler constructor.
     * @param array $options
     */
	public function __construct (array $options = []) {

		$this->options = $options;
	}

    /**
     * @param string $css
     */
	public function setContent ($css) {

	    $this->data = (new Parser($css, $this->options))->parse();
    }

    /**
     * @param $ast
     */
	public function setData ($ast) {

		$this->data = Element::getInstance($ast, $this->options);
	}

    /**
     * @return string
     * @throws \Exception
     */
	public function compile () {

		if (isset($this->data)) {

		    $renderer = !empty($this->options['compress']) ? new Compress($this->options) : new Identity($this->options);

			return $renderer->render($this->data);
		}

		return '';
	}
}
