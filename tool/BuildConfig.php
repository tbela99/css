#!/usr/bin/php
<?php

/**
 * Utility tool to generate the relationship used to compute css properties shorthand. generated is stored in src/config.json
 * @todo add support for background shorthand
 */

require '../test/autoload.php';

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
        ]]]),
    addAlias('-moz-border-radius-topleft', ['-moz-border-radius-topleft' => [
        'alias' => 'border-top-left-radius',
        'shorthand' => '-moz-border-radius'
    ]
    ]),
    addAlias('-moz-border-radius-topright', ['-moz-border-radius-topright' => [
        'alias' => 'border-top-right-radius',
        'shorthand' => '-moz-border-radius'
    ]
    ]),
    addAlias('-moz-border-radius-bottomright', ['-moz-border-radius-bottomright' => [
        'alias' => 'border-bottom-right-radius',
        'shorthand' => '-moz-border-radius'
    ]
    ]),
    addAlias('-moz-border-radius-bottomleft', [
        '-moz-border-radius-bottomleft' => [
            'alias' => 'border-bottom-left-radius',
            'shorthand' => '-moz-border-radius'
        ]
    ])

);

$config['alias'] = array_merge($config['alias'], addAlias('-webkit-border-radius',
    ['-webkit-border-radius' => [
        'alias' => 'border-radius',
        'properties' => [

            '-webkit-border-top-left-radius',
            '-webkit-border-top-right-radius',
            '-webkit-border-bottom-right-radius',
            '-webkit-border-bottom-left-radius'
        ]
    ]
    ]),
    addAlias('-webkit-border-top-left-radius',
        [
            '-webkit-border-top-left-radius' => [
                'alias' => 'border-top-left-radius',
                'shorthand' => '-webkit-border-radius'
            ]
        ]),
    addAlias('-webkit-border-top-right-radius',
        [
            '-webkit-border-top-right-radius' => [
                'alias' => 'border-top-right-radius',
                'shorthand' => '-webkit-border-radius'
            ]
        ]),
    addAlias('-webkit-border-bottom-right-radius',
        [
            '-webkit-border-bottom-right-radius' => [
                'alias' => 'border-bottom-right-radius',
                'shorthand' => '-webkit-border-radius'
            ]
        ]),
    addAlias('-webkit-border-bottom-left-radius',
        [
            '-webkit-border-bottom-left-radius' => [
                'alias' => 'border-bottom-left-radius',
                'shorthand' => '-webkit-border-radius'
            ]
        ]
    ));

file_put_contents(dirname(__DIR__) . '/src/config.json', json_encode($config));

function addAlias($property)
{
    $result = [];
    $args = func_get_args();
    array_shift($args);

    foreach ($args as $arg) {

        if (is_array($arg)) {

            foreach ($arg as $prop => $data) {

                if (!isset($data['shorthand'])) {

                    $data['shorthand'] = $property;
                }

                $result[$prop] = $data;
            }
        }
    }

    return $result;
}

/**
 * @param string $shorthand
 * @param string $pattern
 * @param array $props
 * @param null|string $separator
 * @return array
 */
function makePropertySet($shorthand, $pattern, array $props, $separator = null)
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