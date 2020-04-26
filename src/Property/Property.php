<?php

namespace TBela\CSS\Property;
use ArrayAccess;
use TBela\CSS\ArrayTrait;
use TBela\CSS\Rendererable;
use TBela\CSS\Value;
use TBela\CSS\Value\Set;


/**
 * Css property
 * @package CSS
 */
class Property implements ArrayAccess, Rendererable, RenderableProperty
{
    use ArrayTrait;

    /**
     * @var string
     * @ignore
     */
    protected $name;
    /**
     * @var string
     * @ignore
     */
    protected $type = 'Property';
    /**
     * @var Value[]|null
     * @ignore
     */
    protected $value = [];

    /**
     * Property constructor.
     * @param Value\Set|string$name
     */
    public function __construct($name)
    {

        $this->name = $name;
    }

    /**
     * set the property value
     * @param Set|string $value
     * @return $this
     */
    public function setValue($value) {

        if (is_string($value)) {

            $value = Value::parse($value, $this->name);
        }

        $this->value = $value;
        return $this;
    }

    /**
     * get the property value
     * @return Set|null
     */
    public function getValue() {

        return $this->value;
    }

    /**
     * get the property name
     * @return Set
     */
    public function getName() {

        return $this->name;
    }

    /**
     * return the property type
     * @return string
     */
    public function getType() {

        return $this->type;
    }

    /**
     * get property hash.
     * @return string
     */
    public function getHash() {

        return $this->name.':'.$this->value->render(['css_level' => 4, 'convert_color' => true, 'compress' => true]);
    }

    /**
     * convert property to string
     * @return string
     */
    public function render () {

        return $this->name.': '.$this->value;
    }

    /**
     * convert this object to string
     * @return string
     */
    public function __toString () {

        return $this->render();
    }
}