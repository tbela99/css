<?php

namespace TBela\CSS\Property;

use InvalidArgumentException;
use TBela\CSS\Value;
use TBela\CSS\Value\Set;

/**
 * Compute shorthand properties. Used internally by PropertyList to compute shorthand for properties of the same type
 * @package TBela\CSS\Property
 */
class PropertySet
{

    /**
     * @var array
     * @ignore
     */
    protected $config;

    /**
     * @var Property[]
     * @ignore
     */
    protected $properties = [];

    /**
     * @var array
     * @ignore
     */
    protected $property_type = [];
    /**
     * @var string
     * @ignore
     */
    protected $shorthand;

    /**
     * PropertySet constructor.
     * @param string $shorthand
     * @param array $config
     */
    public function __construct($shorthand, array $config)
    {

        $this->shorthand = $shorthand;

        foreach ($config['properties'] as $property) {

            $config[$property] = Config::getProperty($property);
            $this->property_type[$config[$property]['type']][] = $property;

            unset($config[$property]['shorthand']);
        }

        $this->config = $config;

        if (isset($config['pattern']) && is_array($config['pattern'])) {

            $this->config['pattern'] = $config['pattern'][0];
        }
    }

    /**
     * set property value
     * @param string $name
     * @param Set $value
     * @return PropertySet
     * @throws InvalidArgumentException
     */
    public function set($name, Set $value)
    {

        // is valid property
        if (($this->shorthand != $name) && !in_array($name, $this->config['properties'])) {

            throw new InvalidArgumentException('Invalid property ' . $name, 400);
        }

        // $name is shorthand -> expand
        if ($this->shorthand == $name) {

            $result = $this->expand($value);

            if ($result == false) {

                foreach ($this->config['properties'] as $property) {

                    $this->setProperty($property, $value);
                }
            }

            else {

                foreach ($result as $property => $values) {

                    $separator = Config::getProperty($property.'.separator', ' ');

                    if ($separator != ' ') {

                        $separator = ' '.$separator.' ';
                    }

                    $this->setProperty($property, Value::parse(implode($separator, $values), $property));
                }
            }


        } else {

            $this->setProperty($name, $value);
        }

        return $this;
    }

    /**
     * expand shorthand property
     * @param Set $value
     * @return array|bool
     * @ignore
     */
    protected function expand(Set $value)
    {

        $pattern = explode(' ', $this->config['pattern']);
        $value_map = [];
        $values = [];
        $result = [];

        $separator = isset($this->config['separator']) ? $this->config['separator'] : null;
        $index = 0;

        foreach ($value as $v) {

            if ($v->value == $separator) {

                $index++;
            } else {

                $value_map[$index][] = $v;
            }
        }

        foreach ($pattern as $key => $match) {

            foreach ($value_map as $index => $map) {

                foreach ($map as $i => $v) {

                    if ($v->match($match)) {

                        $values[$index][$match][] = $v;
                        array_splice($value_map[$index], $i, 1);
                        break;
                    }
                }
            }
        }

        // value_map must be empty!!!
        foreach ($value_map as $val) {

            foreach ($val as $v) {

                if ($v->type != 'whitespace' && $v->type != 'separator') {

                    // failure to match the pattern
                    return false;
                }
            }
        }

        foreach ($values as $types) {

            foreach ($this->property_type as $unit => $properties) {

                foreach ($properties as $property) {

                    // value not set
                    if (!isset($types[$unit])) {

                        continue;
                    }

                    $index = array_search($property, $this->property_type[$unit]);
                    $key = null;

                    $list = $types[$unit];

                    if (isset($list[$index])) {

                        $key = $index;
                    } else {

                        if (isset($this->config['value_map'][$property])) {

                                foreach ($this->config['value_map'][$property] as $item) {

                                    if (isset($list[$item])) {

                                        $key = $item;
                                        break;
                                    }
                                }
                        }
                    }

                    if (!is_null($key)) {

                        $result[$property][] = $list[$key];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * convert 'border-radius: 10% 17% 10% 17% / 50% 20% 50% 20% -> 'border-radius: 10% 17% / 50% 20%
     * @return string
     * @ignore
     */
    protected function reduce()
    {
        $result = [];

        foreach ($this->property_type as $unit => $properties) {

            foreach ($properties as $property) {

                if (isset($this->properties[$property])) {

                    $prop = $this->properties[$property];
                    $type = $this->config[$property]['type'];
                    $separator = isset($this->config[$property]['separator']) ? $this->config[$property]['separator'] : ' ';
                    $index = 0;

                    foreach ($prop['value'] as $v) {

                        if ($v->type != 'whitespace' && $v->type != 'separator' && !$v->match($type)) {

                            $result = [];
                            break 3;
                        }

                        if (!is_null($separator) && $v->value == $separator) {

                            $index++;
                        } else {

                            $result[$index][$property] = [$type, $v];
                        }
                    }
                }
            }
        }

        $separator = Config::getProperty($this->config['shorthand'] . '.separator', ' ');

        if ($separator != ' ') {

            $separator = ' '.$separator.' ';
        }

        // does not match the pattern
        // check if properties are set to the same value
        if (empty($result)) {

            if (isset($this->config['value_map']) && count($this->properties) == count($this->config['properties'])) {

                foreach ($this->properties as $key => $property) {

                    $result[$key] = trim($property->getValue()->render(['remove_comments' => true]));
                }

                foreach ($this->config['value_map'] as $key => $mapping) {

                    foreach ($mapping as $value) {

                        if ($result[$key] == $result[$this->config['properties'][$value]]) {

                            unset($result[$key]);
                            break;
                        }

                        break 2;
                    }
                }

                if (count($result) == 1) {

                    reset($this->properties);

                    return current($this->properties)->getValue();
                }
            }

                return false;
        }

        if (isset($this->config['value_map'])) {

            foreach ($result as $index => $values) {

                foreach ($this->config['value_map'] as $property => $set) {

                    $prop = $this->config['properties'][$set[0]];

                    if (isset($values[$property][1]) && isset($values[$prop][1]) && (string) $values[$property][1] == (string) $values[$prop][1]) {

                        unset($values[$property]);
                        continue;
                    }

                    break;
                }

                $result[$index] = trim(preg_replace_callback('#\w+#', function ($matches) use (&$values) {

                    foreach ($values as $key => $property) {

                        if ($property[0] == $matches[0]) {

                            unset($values[$key]);

                            return $property[1];
                        }
                    }

                    return '';

                }, $this->config['pattern']));
            }
        }

        return implode($separator, $result);
    }

    /**
     * set property
     * @param string $name
     * @param Set|string $value
     * @return PropertySet
     * @ignore
     */
    protected function setProperty($name, Set $value)
    {

        $property = $name instanceof Set ? trim($name->render(['remove_comments' => true])) : $name;

        if (!isset($this->properties[$property])) {

            $this->properties[$property] = new Property($name);
        }

        $this->properties[$property]->setValue($value);

        return $this;
    }

    /**
     * return Property array
     * @return Property[]
     */
    public function getProperties() {

        if (count($this->properties) == count($this->config['properties'])) {

            $value = $this->reduce();

            if ($value !== false && $value !== '') {

                return [(new Property($this->config['shorthand']))->setValue($value)];
            }
        }

        return array_values($this->properties);
    }

    /**
     * convert this object to string
     * @param string $join
     * @return string
     */
    public function render($join = "\n")
    {
        $glue = ';';
        $value = '';

        // should use shorthand?
        if (count($this->properties) == count($this->config['properties'])) {

            $value = $this->reduce();

            if ($value !== false) {

                return $this->config['shorthand'].': '.$value;
            }
        }

        foreach ($this->properties as $property) {

            $value .= $property->render() . $glue . $join;
        }

        return rtrim($value, $glue . $join);
    }

    /**
     * convert this object to string
     * @return string
     */
    public function __toString()
    {

        return $this->render();
    }
}