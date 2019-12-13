<?php 

namespace TBela\CSS;

abstract class Element implements \JsonSerializable  {

    protected $ast;
    protected $parent;

    public function __construct($ast, $parent = null) {

        if (\is_null($ast)) {

            $ast = new \stdClass;
            $ast->type = \strtolower(str_replace('Element', '', \get_class($this)));
        }

        $this->ast = $ast;
        $this->setParent($parent);

        if (\is_callable([$this, 'createElements'])) {

            $this->createElements();
        }
    }

	public static function getInstance($ast, $options = []) {

        $type = isset($ast->type) ? $ast->type : '';

        if ($type === '') {

            throw new \InvalidArgumentException('Invalid ast provided');
        }

        $className = __NAMESPACE__.'\Element'.$ast->type;
        
		return new $className($ast);
    }
    
    public function getValue () {

        if (isset($this->ast->value)) {

            return $this->ast->value;
        }

        return '';
    }

    public function setParent($parent) {

        if ($parent == $this->parent) {

            return;
        }
    }

    public function getType() {

        return $this->ast->type;
    }

    public function getParent () {

        return $this->parent;
    }

    public function jsonSerialize () {

        return $this->ast;
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
        return (new Identity())->render($this);
    }
}