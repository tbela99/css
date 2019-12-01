<?php 

namespace CSS;

/**
 * Expose `Compiler`.
 */

class Compiler {

	public $options = [];

	public $indentation = '';
	public $level = 1;

	protected $observers = [];

	public function __construct ($options = []) {

		$this->options = $options;

		if (isset($options['indent'])) {
				
			$this->indentation = $options['indent'];
		}
	}

	public function register ($observer) {

		if (!\in_array($observer, $this->observers)) {
				
			\array_unshift($this->observers, $observer);
		}

		return  $this;
	}

	public function trigger ($event) {

		$args = \func_get_args();

		if (!preg_match('#^on#i', $event)) {

			$event = 'on'.$event;
		}

		foreach ($this->observers as $observer) {

			if (\is_callable ([$observer, $event])) {

				return \call_user_func_array([$observer, $event], array_slice($args, 1));				
			}
		}

		return null;
	}

	public static function getInstance($options = []) {

		$instance = new self($options);
		$instance->register(!empty($options['compress']) ? new Compress($instance) : new Identity($instance));
		return $instance;
	}

	public function compile ($ast) {

		return $this->_compile($ast);
	}

	/**
	 * Emit `str`
	 */

	public function emit ($str) {

		return $this->trigger('emit', $str);
	}

	/**
	 * Visit `node`.
	 */

	public function visit ($node) {

		return \call_user_func([$this, '_'. str_replace('-', '', $node->type)], $node);
	}

	public function mapVisit ($nodes, $type, $delim = '') {

		$buf = '';
		$delim = $this->emit($delim);

		$this->trigger('map', $nodes, $type);
	  
		for ($i = 0, $length = count($nodes); $i < $length; $i++) {
		  $buf .= $this->visit($nodes[$i]);
		  if ($delim && $i < $length - 1) $buf .= $delim;
		}
	  
		return $buf;
	}

	/**
	 * Increase, decrease or return current indentation.
	 */
	
	public function indent($level = null) {
		
		$this->level = is_null($this->level) ? 1 : +$this->level;
	  
		if (null != $level) {
	
		  $this->level += $level;
		  return '';
		}
	  
		return implode(empty($this->indentation) ? '  ' : $this->indentation, array_fill(0, $this->level, ''));
	}
	  
	
	/**
	 * Compile `node`.
	 */
	
	public function _compile($node) {

		return $this->trigger('compile', $this->_stylesheet($node));
	}
	
	/**
	 * Visit stylesheet node.
	 */
	
	public function _stylesheet ($node){
	
		return $this->mapVisit($node->stylesheet->rules, 'rules', "\n");
	}
	
	/**
	 * Visit comment node.
	 */
		
	public function _comment ($node){

		return $this->trigger('comment', $this->indent(0) .$node->comment );
	}
		
	/**
	 * Visit import node.
	 */
	
	public function _import ($node){
	
	//	return $this->emit('@import ' .$node->import . ';');

		return $this->trigger('atrule', 'import', $node->import, '', false);

	}

	public function atrule ($atrule, $name, $rules, $hasBody = true) {
		
		return $this->trigger('atrule', $atrule, $this->emit($name), $this->mapVisit($rules, 'rules', "\n"), $hasBody);
	}
	
/**
 * Visit media node.
 */

	public function _media ($node){

		return $this->atrule('media', $node->media, $node->rules);
	}
		
	/**
	 * Visit document node.
	 */

	public function _document ($node){
		
		return $this->atrule((isset($node->vendor) ? $node->vendor : '').'document', $node->document, $node->rules);
	}

	/**
	 * Visit charset node.
	 */

	public function _charset($node){

		return $this->trigger('atrule', 'charset', $node->charset, '', false);

	//	return $this->trigger('charset', '@charset ' . $node->charset . ';');
	}
	
	/**
	 * Visit namespace node.
	 */

	public function _namespace ($node){

		return $this->trigger('atrule', 'namespace', $node->namespace, '', false);

	//	return $this->emit('@namespace ' . $node->namespace . ';');
	}

	/**
	 * Visit supports node.
	 */

	public function _supports($node){

		return $this->atrule('supports', $node->supports, $node->rules);
	}

	/**
	 * Visit keyframes node.
	 */

	public function _keyframes($node){

		return $this->atrule((isset($node->vendor) ? $node->vendor : '') .'keyframes', $node->name, $node->keyframes);
	}

	public function declaration ($selectors, $rules) {

		return $this->trigger('declarations', $selectors, $this->mapVisit($rules, 'rules', "\n"));
	}

	/**
	 * Visit keyframe node.
	 */

	public function _keyframe($node){

		return $this->declaration($node->values, $node->declarations);
	}

	public function selector($selector) {

		return $this->trigger('selector', $selector);
	}

	/**
	 * Visit page node.
	 */

	public function _page($node){

		return $this->trigger('atrule', 'page', $this->selector(!empty($node->selectors) ? $node->selector : []), $this->mapVisit($node->declarations, 'declarations', "\n"));
	}

	/**
	 * Visit font-face node.
	 */

	public function _fontface ($node){

		return $this->trigger('atrule', 'font-face', '', $this->mapVisit($node->declarations, 'declarations', "\n"));
	}
	
	/**
	 * Visit host node.
	 */

	public function _host($node){

		return $this->trigger('atrule', 'host', '', $this->mapVisit($node->rules, 'rules', "\n"));
	}
	
	/**
	 * Visit custom-media node.
	 */
	
	public function _custommedia($node){

		return $this->trigger('atrule', 'custom-media', $node->name.' ' . $node->media, '', true);
	}
	
	/**
	 * Visit rule node.
	 */
	
	public function _rule($node){
	
		$indent = $this->indent(0);
		$decls = $node->declarations;

		if (empty($decls) && !empty($this->options['remove_empty_nodes'])) return '';
	
		return $this->trigger('declarations', $node->selectors, $this->mapVisit($decls, 'declarations', "\n"));
	}
	
	/**
	 * Visit declaration node.
	 */

	public function _declaration($node){

		return $this->trigger('declaration', $node->property, $node->value);
	}
}
