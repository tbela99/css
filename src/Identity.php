<?php 

namespace CSS;

/**
 * Module dependencies.
 */

class Identity {

	/**
	 * @var Compiler
	 */
	protected $compiler;

	public function __construct(Compiler $compiler) {

		$this->compiler = $compiler;
	}

	public function onCompile ($css) {

		return $css;
	}

	public function onMap($nodes, $type) {

	//	if ($type == 'rules' || $type == 'declarations') {

			// optimize css properties
	//	}

		return $nodes;
	}

	public function onSelector($selector) {

		if (empty($selector)) {

			return '';
		}

		if (!\is_array($selector)) {

			$selector = [$selector];
		}

		return implode($this->compiler->emit(', '), $selector);
	}

	public function onEmit ($value) {

		return $value;
	}

	public function onComment ($value) {

		return $value;
	}

	public function onCharset ($value) {

		return $value;
	}

	public function onAtrule ($atrule, $declaration, $body, $hasBody = true) {

		if ($atrule == 'charset') {

			return '';
		}

		if ($hasBody && !empty($this->compiler->options['remove_empty_nodes']) && trim($body) === '') {

			return '';
		}
		
		$result =  $this->compiler->emit($this->compiler->indent(0).'@'.$atrule).$this->compiler->emit($this->compiler->selector($declaration ? ' '.$declaration : ''));
		
		if ($hasBody) {

			$result .= $this->compiler->emit(
			" {\n".
				$this->compiler->indent(1)).
				$this->compiler->emit($body).
				$this->compiler->emit($this->compiler->indent(-1).
				"\n".$this->compiler->indent(0)."}");
		}

		else {

			$result .= ';';
		}

		return $result;
	}

	public function onDeclarations ($selector, $body) {

		if (!empty($this->compiler->options['remove_empty_nodes']) && trim($body) === '') {

			return '';
		}
		
		return $this->compiler->emit($this->compiler->indent(1)).
			$this->compiler->emit($this->compiler->selector($selector))
			.$this->compiler->emit(
			" {\n"
			.$this->compiler->indent(1))
			.$this->compiler->emit($body)
			.$this->compiler->emit(
			$this->compiler->indent(-1)
			."\n"
			.$this->compiler->indent(-1) 
			."}\n");
	}

	public function onDeclaration ($property, $value) {

		return $this->compiler->emit($this->compiler->indent(0))
			.$this->compiler->emit($property .$this->compiler->emit(': ') . $value)
			.$this->compiler->emit(';');
	}
}


