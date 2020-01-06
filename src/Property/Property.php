<?php

namespace TBela\CSS\Property;
use ArrayAccess;
use TBela\CSS\ArrayTrait;
use TBela\CSS\Rendererable;
use TBela\CSS\Value;
use TBela\CSS\Value\Set;


/**
 * Pretty print CSS
 * @package CSS
 */
class Property implements ArrayAccess, Rendererable, RenderableProperty
{
    use ArrayTrait;

    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $type = 'property';
    /**
     * @var Value[]|null
     */
    protected $value = [];

    /**
     * @param $name
     */

    public function __construct($name)
    {

        $this->name = $name;
    }

    public function getPropertyType() {

        return $this->propertyType;
    }

    public function setValue($value) {

        if (is_string($value)) {

            $value = Value::parse($value);
        }

        $this->value = $value;
        return $this;
    }

    /**
     * @return Value[]|null
     */
    public function getValue() {

        return $this->value;
    }

    public function getName() {

        return $this->name;
    }

    public function getType() {

        return $this->type;
    }

    public function render () {

        return $this->name.': '.$this->value;
    }

    public function __toString () {

        return $this->render();
    }
}