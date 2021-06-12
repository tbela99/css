<?php

namespace TBela\CSS\Property;

use InvalidArgumentException;
use TBela\CSS\Value;
use TBela\CSS\Value\Set;

/**
 * Compute shorthand properties. Used internally by PropertyList to compute shorthand for properties of different types
 * @package TBela\CSS\Property
 */
class PropertyMap
{

    use PropertyTrait;

    /**
     * @var array
     * @ignore
     */
    protected array $config;

    /**
     * @var Property[]
     * @ignore
     */
    protected array $properties = [];

    /**
     * @var array
     * @ignore
     */
    protected array $property_type = [];
    /**
     * @var string
     * @ignore
     */
    protected string $shorthand;

    /**
     * PropertySet constructor.
     * @param string $shorthand
     * @param array $config
     */
    public function __construct(string $shorthand, array $config)
    {

        $this->shorthand = $shorthand;

        $config['required'] = [];

        if (isset($config['properties'])) {

            foreach ($config['properties'] as $property) {

                $config[$property] = Config::getPath('map.' . $property);

                unset($config[$property]['shorthand']);

                $this->property_type[$property] = $config[$property];

                if (empty($config[$property]['optional'])) {

                    $config['required'][] = $property;
                }
            }
        }

        $this->config = $config;
    }

    /**
     * set property value
     * @param string $name
     * @param Set $value
     * @return PropertyMap
     * @throws \Exception
     */
    public function set(string $name, $value, ?array $leadingcomments = null, ?array $trailingcomments = null)
    {

        // is valid property
        if (($this->shorthand != $name) && !in_array($name, $this->config['properties'])) {

            throw new InvalidArgumentException('Invalid property ' . $name, 400);
        }

        if (!($value instanceof Set)) {

            $value = Value::parse($value, $name);
        }

        // the type matches the shorthand - example system font
        if ($name == $this->shorthand || !isset($this->properties[$this->shorthand])) {

            if ($name == $this->shorthand) {

                $this->properties = [];
            }

            if (!isset($this->properties[$name])) {

                $this->properties[$name] = new Property($name);
            }

            $this->properties[$name]->setValue($value)->
                                        setLeadingComments($leadingcomments)->
                                        setTrailingComments($trailingcomments);

            return $this;
        }

        $this->properties[$name] = (new Property($name))->setValue($value)->
                                                setLeadingComments($leadingcomments)->
                                                setTrailingComments($trailingcomments);

//        $values = [];

//        if (isset($this->properties[$this->shorthand])) {

            $values = $this->properties[$this->shorthand]->getValue()->toArray();
//        }
//        else {
//
//            foreach ($this->properties as $property) {
//
//                array_splice($values, count($values), 0, $property->getValue()->toArray());
//            }
//        }

        $data = [];

        foreach ($values as $val) {

            if (in_array($val->type, ['separator', 'whitespace'])) {

                continue;
            }

            if (!isset($data[$val->type])) {

                $data[$val->type] = $val;
            } else {

                if (!is_array($data[$val->type])) {

                    $data[$val->type] = [$data[$val->type]];
                }

                $data[$val->type][] = $val;
            }
        }

        $props = $this->properties;

        unset($props[$this->shorthand]);

        foreach ($props as $k => $prop) {

            $v = $prop->getValue()->toArray();
            $data[$k] = count($v) == 1 ? $v[0] : $v;
        }

        // match
        $patterns = $this->config['pattern'];

        foreach ($patterns as $key => $pattern) {

            foreach (preg_split('#(\s+)#', $pattern, -1, PREG_SPLIT_NO_EMPTY) as $token) {

                if (empty($this->property_type[$token]['optional']) && !isset($data[$token])) {

                    unset($patterns[$key]);
                }
            }
        }

        if (empty($patterns)) {

//            $this->properties[$name] = (new Property($name))->setValue($value)->
//            setLeadingComments($leadingcomments)->
//            setTrailingComments($trailingcomments);
            return $this;
        }

        $properties = $this->property_type;

        // create a map of existing values
        foreach ($data as $datum) {

            foreach ((is_array($datum) ? $datum : [$datum]) as $val) {

                if ($val->type == $this->shorthand) {

                    continue;
                }

                if (in_array($val->type, ['separator', 'whitespace'])) {

                    continue;
                }

                if (isset($data[$val->type])) {

                    if (isset($properties[$val->type]['prefix']) && is_array($properties[$val->type]['prefix'])) {

                        // prefix must be set - example
                        // background: background-position/background-size
                        if (!isset($data[$properties[$val->type]['prefix'][0]['type']])) {

                            return $this;
                        }
                    }

                    // allow multiple values - example font-family
                    if (!empty($properties[$val->type]['multiple'])) {

                        $properties[$val->type]['value'][] = new Set([$val]);
                    } else {

                        $properties[$val->type]['value'][] = is_array($val) ? array_map(function ($v) {
                            return new Set($v);
                        }, $val) : new Set([$val]);
                    }
                }
                // mandatory field
                else if (empty($properties[$val->type]['optional'])) {

                    return $this;
                }
            }
        }

        foreach ($properties as $key => $property) {

            if (!isset($property['value'])) {

                continue;
            }

            if (is_array($property['value'])) {

                $data = ['type' => 'whitespace'];

                if (isset($property['separator'])) {

                    $data = ['type' => 'separator', 'value' => $property['separator']];
                }

                $val = new Set;
                $j = count($property['value']);

                for ($i = 0; $i < $j; $i++) {

                    $val->merge($property['value'][$i]);

                    if ($i < $j - 1) {

                        $val->add(Value::getInstance((object)$data));
                    }
                }

                $properties[$key]['value'] = $val;
            }
        }

        $set = new Set;

        // compute the shorthand and render?
        foreach ($properties as $prop) {

            if (!isset($prop['value'])) {

                continue;
            }

            if (isset($prop['prefix'])) {

                $set->add(Value::getInstance((object)['type' => 'separator', 'value' => is_array($prop['prefix']) ? $prop['prefix'][1] : $prop['prefix']]));
            }

            $set->merge($prop['value']);
            $set->add(Value::getInstance((object)['type' => 'whitespace']));
        }

        $data = Value::reduce($set->toArray(), ['remove_defaults' => true]);

        $this->properties = [$this->shorthand => (new Property($this->shorthand))->setValue(new Set($data))->
        setLeadingComments($leadingcomments)->
        setTrailingComments($trailingcomments)];

        return $this;
    }

    /**
     * set property
     * @param string $name
     * @param Value\Set|string $value
     * @return PropertyMap
     * @ignore
     */
    protected function setProperty($name, $value)
    {

        if (!isset($this->properties[$name])) {

            $this->properties[$name] = new Property($name);
        }

        $this->properties[$name]->setValue($value);

        return $this;
    }

    /**
     * return Property array
     * @return Property[]
     */
    public function getProperties()
    {

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

        foreach ($this->properties as $property) {

            $value .= $property->render() . $glue . $join;
        }

        return rtrim($value, $glue . $join);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {

        return !empty($this->properties);
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