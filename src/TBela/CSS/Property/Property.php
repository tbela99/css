<?php

namespace TBela\CSS\Property;
use ArrayAccess;
use TBela\CSS\ArrayTrait;
use TBela\CSS\Interfaces\RenderableInterface;
use TBela\CSS\Interfaces\RenderablePropertyInterface;
use TBela\CSS\Value;
use TBela\CSS\Value\Set;


/**
 * Css property
 * @package CSS
 */
class Property implements ArrayAccess, RenderableInterface, RenderablePropertyInterface
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
    protected string $type = 'Property';

    protected Set $value;

    /**
     * Property constructor.
     * @param Value\Set|string$name
     */
    public function __construct($name)
    {

        if (is_string($name)) {

            $name = Value::parse($name);
        }
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

        return $this->name->render(['remove_comments' => true]).':'.$this->value->render(['remove_comments' => true, 'css_level' => 4, 'convert_color' => true, 'compress' => true]);
    }

    /**
     * convert property to string
     * @return string
     */
    public function render (array $options = []) {

        return $this->name->render($options).': '.$this->value->render($options);
    }

    /**
     * convert this object to string
     * @return string
     */
    public function __toString () {

        return $this->render();
    }
}