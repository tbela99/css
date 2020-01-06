<?php

namespace TBela\CSS\Value;
use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use TBela\CSS\Value;

/**
 * Pretty print CSS
 * @package CSS
 */
class Set implements IteratorAggregate, JsonSerializable
{

    /**
     * var stdClass;
     */
    protected $data = [];

    public function __construct(array $data)
    {

        $this->data = array_map([Value::class, 'getInstance'], $data);
    }

    /**
     * @return string
     */
    public function render () {

        $result = '';

        $args = func_get_args();

        foreach($this->data as $item) {

            $result .= call_user_func_array([$item, 'render'], $args);
        }

        return $result;
    }

    public function filter (callable $filter) {

        $this->data = array_filter($this->data, $filter);
        return $this;
    }

    public function __toString()
    {
        return $this->render();
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return (string) $this;
    }
}