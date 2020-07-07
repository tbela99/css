<?php

namespace TBela\CSS\Value;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use TBela\CSS\Value;

/**
 * Css values set
 * @package CSS
 */
class Set implements IteratorAggregate, JsonSerializable, Countable
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
    public function __construct(array $data = [])
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
     * @param array $options
     * @return string
     */
    public function render (array $options = []): string {

        $data = $this->data;

        if (!empty($options['remove_comments']) || !empty($options['compress'])) {

            $filter = array_filter($data, function (Value $value) {

                return $value->type != 'comment';
            });

            if (count($filter) != count($data)) {

                $data = Value::reduce($data);
            }
        }

        return implode(','.($options['compress'] ?? false ? '' : ' '), array_map(function ($data) use($options) {

            $result = '';
            foreach($data as $item) {

                $result .= call_user_func([$item, 'render'], $options);
            }

            return $result;
        }, $this->doSplit($this->data, ',')));
    }

    /**
     * filter values
     * @param callable $filter
     * @return $this
     */
    public function filter (callable $filter): Set {

        $this->data = array_filter($this->data, $filter);
        return $this;
    }

    /**
     * map values
     * @param callable $map
     * @return $this
     */
    public function map (callable $map): Set {

        $this->data = array_map($map, $this->data);
        return $this;
    }

    /**
     * append the second set data to the first set data
     * @param Set[] $sets
     * @return Set
     */
    public function merge (Set ...$sets): Set {

        foreach ($sets as $set) {

            array_splice($this->data, count($this->data), 0, $set->data);
        }

        return $this;
    }

    /**
     * split a set according to $separator
     * @param string $separator
     * @return array
     */
    public function split (string $separator): array {

        return $this->doSplit($this->data, $separator);
    }

    protected function doSplit (array $data, string $separator): array {

        if (empty($data)) {

            return [];
        }

        $values = [];

        $current = new Set;

        foreach ($data as $value) {

            if (trim($value)=== $separator) {

                $values[] = $current;
                $current = new Set;
            }

           else {

                $current->data[] = clone $value;
            }
        }

        if (end($values) !== $current) {

            $values[] = $current;
        }

        return $values;
    }

    /**
     * append the second set data to the first set data
     * @param int $index
     * @param int|null $length
     * @param Set[] $replacement
     * @return Set
     */
    public function splice (int $index, int $length = null, Set ...$replacement): Set {

        $value = array_splice($this->data, $index, $length, $replacement);
        return new Set([$value]);
    }

    /**
     * add an item to the set
     * @param Value $value
     * @return $this
     */
    public function add(Value $value): Set {

        $this->data[] = $value;
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
     * return an array of internal data
     * @return array
     */
    public function toArray(): array {

        return $this->data;
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

    /**
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }
}