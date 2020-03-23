<?php

namespace TBela\CSS\Value;
use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use TBela\CSS\Value;

/**
 * Css values set
 * @package CSS
 */
class Set implements IteratorAggregate, JsonSerializable
{
    /**
     * @var array
     * @ignore
     */
    protected $data = [];

    /**
     * Set constructor.
     * @param array[Value] $data
     */
    public function __construct(array $data)
    {

        $this->data = array_map([Value::class, 'getInstance'], $data);
    }

    /**
     * @param string $name
     * @return mixed|null
     * @ignore
     */
    public function __get($name)
    {
        if(isset($this->data[$name])) {

            return $this->data[$name];
        }

        return null;
    }

    /**
     * Convert this object to string
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

    /**
     * filter values
     * @param callable $filter
     * @return $this
     */
    public function filter (callable $filter) {

        $this->data = array_filter($this->data, $filter);
        return $this;
    }

    /**
     * map values
     * @param callable $map
     * @return $this
     */
    public function map (callable $map) {

        $this->data = array_map($this->data, $map);
        return $this;
    }

    /**
     * Automatically convert this object to string
     * @return string
     */
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