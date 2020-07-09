<?php

namespace TBela\CSS\Property;

use ArrayAccess;
use TBela\CSS\ArrayTrait;
use TBela\CSS\Interfaces\RenderableInterface;
use TBela\CSS\Value;
use TBela\CSS\Value\Set;

/**
 * Comment property class
 * @package TBela\CSS\Property
 */
class Comment implements ArrayAccess, RenderableInterface {

    use ArrayTrait;

    /**
     * @var string|Value|Set
     * @ignore
     */
    protected $value;

    /**
     * @var string
     * @ignore
     */
    protected $type = 'Comment';

    /**
     * PropertyComment constructor.
     * @param Set | Value | string $value
     */
    public function __construct($value)
    {

        $this->setValue($value);
    }

    /**
     * Set the value
     * @param Set | Value | string $value
     * @return $this
     */
    public function setValue($value) {

        if (!($value instanceof Set)) {

            $value = Value::parse($value);
        }

        $this->value = $value;
        return $this;
    }

    /**
     * Return the object value
     * @return Set
     */
    public function getValue() {

        return $this->value;
    }

    /**
     * return the object type
     * @return string
     */
    public function getType () {

        return $this->type;
    }

    /**
     * Converty this object to string
     * @param bool $compress
     * @param array $options
     * @return string
     */
    public function render (array $options = []) {

        if (!empty($options['remove_comments'])) {

            return '';
        }

        return $this->value;
    }

    /**
     * Automatically convert this object to string
     * @return string
     */
    public function __toString()
    {

        return $this->render();
    }
}