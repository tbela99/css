<?php

namespace TBela\CSS\Property;

use ArrayIterator;
use Exception;
use InvalidArgumentException;
use IteratorAggregate;
use stdClass;
use TBela\CSS\RuleList;
use TBela\CSS\Value;
use Traversable;
use function preg_match;

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

    public function __construct(RuleList $list = null, $options = [])
    {
        $this->options = $options;

        if ((is_callable([$list, 'hasDeclarations']) && $list->hasDeclarations()) || $list instanceof Rule) {

            foreach ($list as $element) {

                $this->set($element['name'], $element['value'], $element['type']);
            }
        }

    }

    public function set($name, $value, $propertyType = null) {

        if ($propertyType == 'Comment') {

            $this->properties[] = new Comment($value);
            return $this;
        }

        $name = (string) $name;

        if (!Config::exists($name)) {

            if(!empty($this->options['allow_duplicate_declarations'])) {

                if ($this->options['allow_duplicate_declarations'] === true ||
                    (is_array($this->options['allow_duplicate_declarations']) && in_array($name, $this->options['allow_duplicate_declarations']))) {

                    $this->properties[] = (new Property($name, $propertyType))->setValue($value);
                    return $this;
                }
            }
        }

        $value = is_string($value) ? Value::parse($value) : $value;
        $alias_name = Config::alias($name.'.alias', $name);
        $config = Config::getProperty($alias_name);

        // is is an expanded property?
        if (!is_null($config)) {

            $shorthand = Config::alias($name.'.shorthand', $config['shorthand']);

           $config = Config::getProperty($shorthand, $config);

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

    public function render ($glue = ';', $join = "\n")
    {

        $result = '';

        foreach ($this->getProperties() as $property) {

            $result .= $property->render($glue, $join);

            if (!($property instanceof Comment)) {

                $result .= $glue;
            }

            $result .= $join;
        }

        return rtrim(rtrim($result), $glue);
    }

    public function __toString() {

        return $this->render();
    }

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
