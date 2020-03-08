<?php

namespace TBela\CSS\Property;

use InvalidArgumentException;
use TBela\CSS\Value;

class PropertySet
{

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var array
     */
    protected $property_type = [];
    /**
     * @var string
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

        if ($this->shorthand != $config['shorthand']) {

            $value_map = [];
            $config['shorthand'] = $shorthand;
            $properties = Config::alias($shorthand.'.properties', []);

            $config['properties'] = $properties;

            foreach (array_reverse($properties) as $property) {

                $alias = Config::alias($property.'.alias');

            //    if (isset($config[$alias])) {

                    $config[$property] = Config::getProperty($alias);
                 //   unset($config[$alias]);
            //    }

                if (isset($config['value_map'])) {

                    if (isset($config['value_map'][$alias])) {

                        $value_map[$property] = $config['value_map'][$alias];
                    }
                }
            }

            if (!empty($value_map)) {

                $config['value_map'] = $value_map;
            }
        }

        else if (isset($config['properties'])) {

            foreach ($config['properties'] as $property) {

                $config[$property] = Config::getProperty($property);
            }
        }

        foreach ($config['properties'] as $property) {

            if (!isset($config[$property]['type'])) {

                var_dump($shorthand, $property, $config[$property]);
                echo (new \Exception())->getTraceAsString();
                die;
            }


            $this->property_type[$config[$property]['type']][] = $property;
        }

        $this->config = $config;
    }

    /**
     * @param string $name
     * @param Value[]|string $value
     * @return PropertySet
     * @throws InvalidArgumentException
     */
    public function set($name, $value)
    {

        if (is_string($value)) {

            $value = Value::parse($value);
        }

        // is valid property
        if (($this->shorthand != $name) && !in_array($name, $this->config['properties'])) {

            throw new InvalidArgumentException('Invalid property ' . $name, 400);
        }

        // $name is shorthand -> expand
        if ($this->shorthand == $name) {

            $this->expand($value);
        } else {

            $this->setProperty($name, $value);
        }

        return $this;
    }

    /**
     * expand shorthand property
     * @param Value[] $value
     * @return PropertySet
     */
    protected function expand($value)
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

        foreach ($pattern as $match) {

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

                        if (isset($this->config['value_map'])) {

                            $value_map = $this->config['value_map'];

                            foreach ($value_map as $i) {

                                foreach ($i as $item) {

                                    if (isset($list[$item])) {

                                        $key = $item;
                                        break 2;
                                    }
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

        foreach ($result as $property => $values) {

            $separator = Config::getProperty($property.'.separator', ' ');

            if ($separator != ' ') {

                $separator = ' '.$separator.' ';
            }

            $this->setProperty($property, implode($separator, $values));
        }

        return $this;
    }

    /**
     * convert 'border-radius: 10% 17% 10% 17% / 50% 20% 50% 20% -> 'border-radius: 10% 17% / 50% 20%
     * @return string
     */
    protected function reduce()
    {
        $result = [];

        foreach ($this->property_type as $unit => $properties) {

            foreach ($properties as $property) {

                if (isset($this->properties[$property])) {

                    $data = $this->properties[$property];
                    $type = $this->config[$property]['type'];
                    $separator = isset($this->config[$property]['separator']) ? $this->config[$property]['separator'] : ' ';
                    $index = 0;

                    foreach ($data['value'] as $v) {

                        if (!is_null($separator) && $v->value == $separator) {

                            $index++;
                        } else {

                            $result[$index][$property] = [$type, $v];
                        }
                    }
                }
            }
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

        $separator = Config::getProperty($this->config['shorthand'] . '.separator', ' ');

        if ($separator != ' ') {

            $separator = ' '.$separator.' ';
        }

        return implode($separator, $result);
    }

    /**
     * @param $name
     * @param $value
     */
    protected function setProperty($name, $value)
    {

        if (!isset($this->properties[$name])) {

            $this->properties[$name] = new Property($name, Config::getProperty($name . '.type'));
        }

        $this->properties[$name]->setValue($value);
    }

    public function getProperties() {

        if (count($this->properties) == count($this->config['properties'])) {

            return [(new Property($this->config['shorthand']))->setValue($this->reduce())];
        }

        return array_values($this->properties);
    }

    /**
     * @param string $join
     * @return string
     */
    public function render($join = "\n")
    {
        $glue = ';';
        $value = '';

        // should use shorthand?
        if (count($this->properties) == count($this->config['properties'])) {

            return $this->config['shorthand'].': '.$this->reduce();
        }

        foreach ($this->properties as $property) {

            $value .= $property->render() . $glue . $join;
        }

        return rtrim($value, $glue . $join);
    }

    public function __toString()
    {

        return $this->render();
    }
}