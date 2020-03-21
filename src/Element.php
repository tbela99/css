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

abstract class Element implements JsonSerializable, ArrayAccess, Rendererable   {

    use ArrayTrait;

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
            $ast->type = str_ireplace(Element::class.'\\', '', get_class($this));
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

            $ast = clone $ast->ast;
        }

        if (isset($ast->type)) {

            $type = $ast->type;
            unset($ast->parsingErrors);
        }

        if ($type === '') {

            throw new InvalidArgumentException('Invalid ast provided');
        }

        $className = Element::class.'\\'.ucfirst($ast->type);
        
		return new $className($ast);
    }

    /**
     * return the root element
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
     * @return Value\Set|Value|string
     */
    public function getValue () {

        if (isset($this->ast->value)) {

            return $this->ast->value;
        }

        return '';
    }

    /**
     * @param Value\Set|Value|string $value
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

        if (isset($this->ast->elements) && empty($this->ast->elements)) {

            $ast = clone $this->ast;

            unset ($ast->elements);
            return $ast;
        }

        return $this->ast;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function __toString()
    {
        try {

            return (new Renderer())->render($this, null, true);
        }

        catch (Exception $ex) {

            error_log($ex->getTraceAsString());
        }

        return '';
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