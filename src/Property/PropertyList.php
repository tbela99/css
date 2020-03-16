<?php

namespace TBela\CSS\Property;

use ArrayIterator;
use IteratorAggregate;
use TBela\CSS\Value;
use TBela\CSS\RuleList;
use TBela\CSS\Element\Rule;

/**
 * Pretty print CSS
 * @package CSS
 */
class PropertyList implements IteratorAggregate
{

    /**
     * @var Property[]
     */
    protected $properties = [];

    /**
     * @var array
     */
    protected $options = [];

    /***
     * PropertyList constructor.
     * @param RuleList|null $list
     * @param array $options
     */
    public function __construct(RuleList $list = null, $options = [])
    {
        $this->options = $options;

        if ((is_callable([$list, 'hasDeclarations']) && $list->hasDeclarations()) || $list instanceof Rule) {

            foreach ($list as $element) {

                $this->set($element['name'], $element['value'], $element['type']);
            }
        }

    }

    /**
     * @param string $name
     * @param Value|string $value
     * @param string|null $propertyType
     * @return $this
     */
    public function set($name, $value, $propertyType = null) {

        if ($propertyType == 'Comment') {

            $this->properties[] = new Comment($value);
            return $this;
        }

        $name = (string) $name;

        if(!empty($this->options['allow_duplicate_declarations'])) {

            if ($this->options['allow_duplicate_declarations'] === true ||
                (is_array($this->options['allow_duplicate_declarations']) && in_array($name, $this->options['allow_duplicate_declarations']))) {

                $this->properties[] = (new Property($name, $propertyType))->setValue($value);
                return $this;
            }
        }

        $value = is_string($value) ? Value::parse($value) : $value;
        $shorthand = Config::getProperty($name.'.shorthand');

        // is is an expanded property?
        if (!is_null($shorthand)) {

           $config = Config::getProperty($shorthand);

            if (!isset($this->properties[$shorthand])) {

                $this->properties[$shorthand] = new PropertySet($shorthand, $config);
            }

            $this->properties[$shorthand]->set($name, $value);
        }

        else {

            // regular property
            if (!isset($this->properties[$name])) {

                $this->properties[$name] = new Property($name, Config::getProperty($name . '.type'));
            }

            $this->properties[$name]->setValue($value);
        }

        return $this;
    }

    /**
     * @param string $glue
     * @param string $join
     * @return string
     */
    public function render ($glue = ';', $join = "\n")
    {

        $result = [];

        foreach ($this->getProperties() as $property) {

            $output = $property->render($glue, $join);

            if (!($property instanceof Comment)) {

                $output .= $glue;
            }

            $output .= $join;
            $result[] = $output;
        }

        return rtrim(rtrim(implode('', $result)), $glue);
    }

    public function __toString() {

        return $this->render();
    }

    /**
     * @return ArrayIterator<Property>
     */
    public function getProperties () {

        $result = [];

        foreach ($this->properties as $property) {

            if ($property instanceof PropertySet) {

                array_splice($result, count($result), 0, $property->getProperties());
            }

            else {

                $result[] = $property;
            }
        }

        $hashes = [];

        $i = count($result);

        // remove duplicate values
        // color: #f00;
        // color: red;
        // ...
        // color: rbg(255, 0, 0);
        // compute all to the last value -> color: red

        while($i--) {

            if ($result[$i]['type'] == 'Comment') {

                continue;
            }

            $hash = $result[$i]['hash'];

            if (isset($hashes[$hash])) {

                array_splice($result, $i, 1);
            }

            else {

                $hashes[$hash] = 1;
            }
        }

        return new ArrayIterator($result);
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return $this->getProperties();
    }
}
