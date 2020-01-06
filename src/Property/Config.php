<?php

namespace TBela\CSS\Property;

Config::load(dirname(__DIR__).'/config.json');

final class Config {

    /**
     * @var array
     */
    protected static $config = [
        'properties' => [],
        'alias' => []
    ];

    /**
     * load config from a file
     * @param $file
     */
    public static function load($file) {

        static::$config = json_decode(file_get_contents($file), true);
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function exists($path) {

        $found = true;
        $item = static::$config['alias'];

        foreach (explode('.', $path) as $p) {

            if (isset($item[$p])) {

                $item = $item[$p];
                continue;
            }

            $found = false;
            break;
        }

        if (!$found) {

            $found = true;
            $item = static::$config['properties'];

            foreach (explode('.', $path) as $p) {

                if (isset($item[$p])) {

                    $item = $item[$p];
                    continue;
                }

                $found = false;
            }
        }

        return $found;
    }

    /**
     * return property alias
     * @param string $property
     * @param mixed|null $default
     * @return string
     */
    public static function alias ($property, $default = null) {

        if (is_array($property)) {

            echo (new \Exception())->getTraceAsString();
        }

        if (strpos($property, '.') > 0) {

            return static::getPath('alias.'.$property, $default);
        }

        if (isset(static::$config['alias'][$property])) {

            return static::$config['alias'][$property];
        }

        return $property;
    }

    /**
     * @param string $path
     * @param mixed|null $default
     * @return mixed|null
     */
    protected static function getPath($path, $default = null) {

        $data = static::$config;

        foreach (explode('.', $path) as $item) {

            if (!isset($data[$item])) {

                return $default;
            }

            $data = $data[$item];
        }

        return $data;
    }

    /**
     * @param string|null $name
     * @param mixed|null $default
     * @return array|mixed|null
     */
    public static function getProperty ($name = null, $default = null) {

        if (is_null($name)) {

            return static::$config;
        }

        if (is_array($name)) {

            echo (new \Exception())->getTraceAsString();
        }

        if (isset(static::$config['properties'][$name])) {

            return static::$config['properties'][$name];
        }

        if (strpos($name, '.') > 0) {

            return static::getPath('properties.'.$name, $default);
        }

        return $default;
    }

    /**
     * @param $property
     * @param alias, ... property aliases
     */
    public static function addAlias ($property) {

        $properties = [];

        $args = func_get_args();

        array_shift($args);

        foreach ($args as $arg) {

            static::$config['alias'][$arg] = $property;
        }
    }

    /**
     * @param $shorthand
     * @param $pattern
     * @param $properties
     * @param bool $separator allow multiple values
     *
     * @return array
     */
    public static function addSet ($shorthand, $pattern, $properties, $separator = null) {

        $config = [];

        $config[$shorthand] = [

            'shorthand' => $shorthand,
            'pattern' => $pattern,
            'value_map' => []
        ];

        if (!is_null($separator)) {

            $config[$shorthand]['separator'] = $separator;
        }

        $value_map_keys = [];

        // build value map
        foreach ($properties as $property => $data) {

            $value_map_keys[$data['type']][] = $property;
        }

        foreach ($properties as $property => $data) {

            $config[$shorthand]['properties'][] = $property;

            if (isset($data['value_map'])) {

                $map_keys = $value_map_keys[$properties[$property]['type']];

                $config[$shorthand]['value_map'][$property] = array_map(function ($value) use ($map_keys) {

                    return array_search($value, $map_keys);

                }, $data['value_map']);

                unset($data['value_map']);
            }

            $data['shorthand'] = $shorthand;
            $config[$property] = $data;
        }

        if (isset($config[$shorthand]['value_map'])) {

            $config[$shorthand]['value_map'] = array_reverse($config[$shorthand]['value_map']);
        }

        static::$config['properties'] = array_merge(static::$config['properties'], $config);

        return $config;
    }
}

