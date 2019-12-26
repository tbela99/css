<?php 

namespace TBela\CSS;

use Exception;
use InvalidArgumentException;
use JsonSerializable;
use ArrayAccess;
use stdClass;
use function get_class;
use function is_callable;
use function is_null;
use function strtolower;
use function str_ireplace;

abstract class Element implements JsonSerializable, ArrayAccess   {

    protected $ast = null;
    /**
     * @var RuleList
     */
    protected $parent = null;

    /**
     * Element constructor.
     * @param object|null $ast
     * @param RuleList|null $parent
     * @throws Exception
     */
    public function __construct($ast = null, RuleList $parent = null) {

        if (is_null($ast)) {

            $ast = new stdClass;
            $ast->type = strtolower(str_ireplace(__NAMESPACE__.'\Element', '', get_class($this)));
        }

        $this->ast = $ast;

        if (!is_null($parent)) {

            $parent->append($this);
        }

        if (is_callable([$this, 'createElements'])) {

            $this->createElements();
        }
    }

    /**
     * @param Element|object $ast
     * @return mixed
     * @throws InvalidArgumentException
     */
	public static function getInstance($ast) {

        $type = '';

        if ($ast instanceof Element) {

            $ast = json_decode(json_encode($ast));
        }

        if (isset($ast->type)) {

            $type = str_ireplace(__NAMESPACE__, '', $ast->type);
        }

        if ($type === '') {

            throw new InvalidArgumentException('Invalid ast provided');
        }

        $className = __NAMESPACE__.'\Element'.$ast->type;
        
		return new $className($ast);
    }

    /**
     * @return Element
     */
    public function getRoot () {

        $element = $this;

        while ($parent = $element->parent) {

            $element = $parent;
        }

        return $element;
    }

    /**
     * @return string
     */
    public function getValue () {

        if (isset($this->ast->value)) {

            return $this->ast->value;
        }

        return '';
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue ($value) {

        $this->ast->value = $value;
        return $this;
    }

    /**
     * @return RuleList|null
     */
    public function getParent () {

        return $this->parent;
    }

    /**
     * @return string
     */
    public function getType() {

        return $this->ast->type;
    }

    /**
     * Clone parents, children and the element itself. Useful when you want to render this element only and its parents.
     * @return Element
     */
    public function copy() {

        $parent = $this;
        $node = clone $this;

        while ($parent = $parent->parent) {

            $ast = clone $parent->ast;

            if (isset($ast->elements)) {

                $ast->elements = [];
            }

            $parentNode = Element::getInstance($ast);
            $parentNode->append($node);
            $node = $parentNode;
        }

        return $node;
    }

    /**
     * @return stdClass
     */
    public function jsonSerialize () {

        return $this->ast;
    }

    public function offsetSet($offset, $value) {

        if (is_callable([$this, 'set'.$offset])) {

            call_user_func([$this, 'set'.$offset], $value);
        }
    }

    public function offsetExists($offset) {
        return is_callable([$this, 'get'.$offset]) || is_callable([$this, 'set'.$offset]);
    }

    public function offsetUnset($offset) {

        if (is_callable([$this, 'set'.$offset])) {

            call_user_func([$this, 'set'.$offset], null);
        }
    }

    public function offsetGet($offset) {

        return is_callable([$this, 'get'.$offset]) ? call_user_func([$this, 'get'.$offset]): null;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function __toString()
    {
        try {

            return (new Identity())->render($this, null, true);
        }

        catch (Exception $ex) {

            echo $ex->getTraceAsString();
        }
    }

    public function __clone()
    {
        $this->ast = clone $this->ast;
        $this->parent = null;

        if (isset($this->ast->elements)) {

            foreach ($this->ast->elements as $key => $value) {

                $this->ast->elements[$key] = clone $value;
                $this->ast->elements[$key]->parent = $this;
            }
        }
    }
}