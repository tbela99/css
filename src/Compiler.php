<?php 

namespace TBela\CSS;

/**
 * Expose `Compiler`.
 */

class Compiler {

    protected $options = [];
	protected $defaultOptions = [
	    'indent' => ' ',
        'glue' => "\n",
        'separator' => ' ',
        'charset' => false,
        'rgba_hex' => false,
        'compress' => false,
        'remove_comments' => false,
        'remove_empty_nodes' => false
    ];

	protected $data;

    /**
     * Compiler constructor.
     * @param array $options
     */
	public function __construct (array $options = []) {

        $this->options = $this->defaultOptions;
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
     */
	public function setContent ($css) {

	    $this->data = (new Parser($css, $this->options))->parse();
	    return $this;
    }

    /**
     * @param $ast
     */
	public function setData ($ast) {

		$this->data = Element::getInstance($ast, $this->options);
		return $this;
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
