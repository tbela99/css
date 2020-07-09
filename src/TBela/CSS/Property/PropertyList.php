<?php

namespace TBela\CSS\Property;

use ArrayIterator;
use IteratorAggregate;
use TBela\CSS\Value;
use TBela\CSS\RuleList;
use TBela\CSS\Element\Rule;
use TBela\CSS\Value\Set;

/**
 * Property list
 * @package CSS
 */
class PropertyList implements IteratorAggregate
{

    /**
     * @var Property[]
     * @ignore
     */
    protected array $properties = [];

    /**
     * @var array
     * @ignore
     */
    protected array $options = [];

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
     * set property
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

        if (!($name instanceof Set)) {

            $name = Value::parse($name);
        }

        $propertyName = strtolower($name->render(['remove_comments' => true]));

        if (is_string($value) || is_numeric($value)) {

            $value = Value::parse($value, $name);
        }
//        $propertyName
        if(!empty($this->options['allow_duplicate_declarations'])) {

            if ($this->options['allow_duplicate_declarations'] === true ||
                (is_array($this->options['allow_duplicate_declarations']) && in_array($name, $this->options['allow_duplicate_declarations']))) {

                $this->properties[] = (new Property($name, $propertyType))->setValue($value);
                return $this;
            }
        }

        $shorthand = Config::getProperty($propertyName.'.shorthand');

        // is is an shorthand property?
        if (!is_null($shorthand)) {

           $config = Config::getProperty($shorthand);

            if (!isset($this->properties[$shorthand])) {

                $this->properties[$shorthand] = new PropertySet($shorthand, $config);
            }

            $this->properties[$shorthand]->set($name, $value);
        }

        else {

            $shorthand = Config::getPath('map.'.$propertyName.'.shorthand');

            // is is an shorthand property?
            if (!is_null($shorthand)) {

                $config = Config::getPath('map.'.$shorthand);

                if (!isset($this->properties[$shorthand])) {

                    $this->properties[$shorthand] = new PropertyMap($shorthand, $config);
                }

                $this->properties[$shorthand]->set($name, $value);
            }

            else {

                // regular property
                if (!isset($this->properties[$propertyName])) {

                    $this->properties[$propertyName] = new Property($name, Config::getProperty($name . '.type'));
                }

                $this->properties[$propertyName]->setValue($value);
            }

        }

        return $this;
    }

    /**
     * convert properties to string
     * @param string $glue
     * @param string $join
     * @return string
     */
    public function render ($glue = ';', $join = "\n")
    {

        $result = [];

        foreach ($this->getProperties() as $property) {

            $output = $property->render($this->options);

            if (!($property instanceof Comment)) {

                $output .= $glue;
            }

            $output .= $join;
            $result[] = $output;
        }

        return rtrim(rtrim(implode('', $result)), $glue);
    }

    /**
     * convert this object to string
     * @return string
     */
    public function __toString() {

        return $this->render();
    }

    /**
     * return properties iterator
     * @return ArrayIterator<Property>
     */
    public function getProperties () {

        /**
         * @var Property[] $result
         */
        $result = [];

        foreach ($this->properties as $property) {

            if (is_callable([$property, 'getProperties'])) {

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

            $hash = $result[$i]->getHash();

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