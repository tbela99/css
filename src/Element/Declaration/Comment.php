<?php

namespace TBela\CSS\Element\Declaration;

use TBela\CSS\Interfaces\RenderableInterface;

/**
 * Comment property class
 * @package TBela\CSS\Property
 */
class Comment extends Property {

    /**
     * @var string
     * @ignore
     */
    protected string $type = 'Comment';

    /**
     * PropertyComment constructor.
     * @param array | string $value
     */
    public function __construct($value)
    {

        $this->setValue($value);
    }

    public function getName(bool $vendor = false) {

        return null;
    }

    /**
     * Set the value
     * @param array | string $value
     * @return $this
     */
    public function setValue($value) {

        $this->value = $value;
        return $this;
    }

    /**
     * Return the object value
     * @return string
     */
    public function getValue() {

        return $this->value;
    }

    /**
     * Converty this object to string
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
     * @inheritDoc
     */
    public function setTrailingComments(?array $comments): RenderableInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTrailingComments(): ?array
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function setLeadingComments(?array $comments): RenderableInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLeadingComments(): ?array
    {
        return null;
    }
}