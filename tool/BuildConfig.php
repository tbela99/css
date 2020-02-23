#!/usr/bin/php
<?php

require '../test/autoload.php';

/**
 * Utility file to build css properties dependency
 */

// properties order is important!
use TBela\CSS\Property\Config;

$config = [
    'properties' => [],
    'alias' => []
];

$config['properties'] = makePropertySet('margin', 'unit unit unit unit', [
    ['margin-top', 'unit'],
    ['margin-right', 'unit'],
    ['margin-bottom', 'unit'],
    ['margin-left', 'unit']
]);

$config['properties'] = array_merge($config['properties'], makePropertySet('padding', 'unit unit unit unit', [
    ['padding-top', 'unit'],
    ['padding-right', 'unit'],
    ['padding-bottom', 'unit'],
    ['padding-left', 'unit']
]));

$config['properties'] = array_merge($config['properties'], makePropertySet('border-radius', 'unit unit unit unit', [
    ['border-top-left-radius', 'unit', ' '],
    ['border-top-right-radius', 'unit', ' '],
    ['border-bottom-right-radius', 'unit', ' '],
    ['border-bottom-left-radius', 'unit', ' ']
], '/'));

$config['alias'] = array_merge($config['alias'], addAlias('-moz-border-radius',
    ['-moz-border-radius' => [
        'alias' => 'border-radius',
        'properties' => [

            '-moz-border-radius-topleft',
            '-moz-border-radius-topright',
            '-moz-border-radius-bottomright',
            '-moz-border-radius-bottomleft'
        ]]]));

$config['alias'] = array_merge($config['alias'], addAlias('-webkit-border-radius',
    ['-webkit-border-radius' => [
        'alias' => 'border-radius',
        'properties' => [

            '-webkit-border-top-left-radius',
            '-webkit-border-top-right-radius',
            '-webkit-border-bottom-right-radius',
            '-webkit-border-bottom-left-radius'
        ]
    ]]));

file_put_contents(dirname(__DIR__) . '/src/config.json', json_encode($config, JSON_PRETTY_PRINT));

function addAlias($property)
{

    global $config;

    $result = [];
    $args = func_get_args();
    array_shift($args);

    foreach ($args as $arg) {

        if (is_array($arg)) {

            foreach ($arg as $prop => $data) {

                $data['shorthand'] = $property;
                $result[$prop] = $data;
            }
        }
    }

    return $result;
}

/**
 * @param $shorthand
 * @param $pattern
 * @param $props
 * @param null $separator
 * @return array
 */
function makePropertySet($shorthand, $pattern, $props, $separator = null)
{

    $properties = [];

    foreach ($props as $key => $prop) {

        $properties[$prop[0]] = ['type' => $prop[1]];

        if ($key > 0 && $key < 3) {

            $properties[$prop[0]]['value_map'] = [$props[0][0]];
        }

        if ($key == 3) {

            $properties[$prop[0]]['value_map'] = [$props[1][0], $props[0][0]];
        }

        if (isset($props[1][2])) {

            $properties[$prop[0]]['separator'] = $props[1][2];
        }
    }

    return Config::addSet($shorthand, $pattern, $properties, $separator);
}