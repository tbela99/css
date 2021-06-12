#!/usr/bin/php
<?php

/**
 * Utility tool to generate the relationship used to compute css properties shorthand.
 * generated is stored in src/TBela/CSS/config.json
 * @todo add support for background shorthand
 */

require __DIR__ . '/../test/autoload.php';

// properties order is important!
use TBela\CSS\Property\Config;

$config = [
    // shorthand that can be computed only when every shorthand property is defined because it will override properties that are not directly handled.
    // the shorthand should not override longhand properties
    // example: font
    'map' => [],
    // shorthand that can be safely computed
    // the shorthand overrides all longhand properties
    // example: margin, padding, border-radius, ...
    'properties' => [],
    // properties aliases
    'alias' => []
];

$config['properties'] = array_merge($config['properties'], makePropertySet('background-repeat', ['background-repeat', 'background-repeat background-repeat'], [], ',', false, null, false));

$config['map'] = array_merge($config['map'], makePropertySet('background', ['background', 'background-image background-color background-position background-size background-repeat background-attachment background-clip background-origin'], [
    ['background-image',
        ['type' => 'background-image', 'optional' => true]
    ],
    ['background-color',
        ['type' => 'background-color', 'optional' => true]
    ],
    ['background-position',
        ['type' => 'background-position', 'multiple' => true, 'optional' => true]
    ],
    ['background-size',
        [
            'type' => 'background-size', 'multiple' => true, 'optional' => true,
            'prefix' => [
                ['type' => 'background-position'],
                '/'
            ]
        ]
    ],
    ['background-repeat',
        ['type' => 'background-repeat', 'multiple' => true, 'optional' => true]
    ],
    ['background-attachment',
        ['type' => 'background-attachment', 'optional' => true]
    ],
    ['background-clip',
        ['type' => 'background-clip', 'optional' => true]
    ],
    ['background-origin',
        ['type' => 'background-origin', 'multiple' => true, 'optional' => true]
    ]
], ','));

//$config['map'] = array_merge($config['map'], makePropertySet('background-attachment', ['background-attachment'], [
//        ['background-attachment',
//            ['type' => 'background-attachment', 'optional' => true]
//    ]
//], ',', false));

//$config['properties'] = array_merge($config['properties'], makePropertySet('background-image', ['background-image'], [
//    ['background-image',
//        ['type' => 'background-image', 'optional' => true]
//    ]
//], ',', false, null, 'background'));

$config['properties'] = array_merge($config['properties'], makePropertySet('background-size', ['background-size', 'unit unit'], [], ',', false, null,false));

$config['map'] = array_merge($config['map'], makePropertySet('font', ['font', 'font-weight font-style font-variant font-stretch font-size line-height font-family'], [
    ['font-weight',
        ['type' => 'font-weight', 'optional' => true]
    ],
    ['font-style',
        ['type' => 'font-style', 'optional' => true]
    ],
    ['font-variant',
        ['type' => 'font-variant', 'optional' => true]
    ],
    ['font-stretch',
        ['type' => 'font-stretch', 'optional' => true]
    ],
    ['font-size',
        ['type' => 'font-size']
    ],
    ['line-height',
        ['type' => 'line-height', 'optional' => true, 'previous' => 'font-size', 'prefix' => '/']
    ],
    ['font-family',
        ['type' => 'font-family', 'multiple' => true, 'separator' => ',']
    ]
], null, false));

$config['map'] = array_merge($config['map'], makePropertySet('outline', ['outline-style outline-width outline-color'], [
    ['outline-style',
        ['type' => 'outline-style', 'optional' => true]
    ],
    ['outline-width',
        ['type' => 'outline-width', 'optional' => true]
    ],
    ['outline-color',
        ['type' => 'outline-color', 'optional' => true]
    ]
], null, false,
    /**
     *compute shorthand property
     */
    ['compute' => true]));
//
//$config['properties'] = array_merge($config['properties'], makePropertySet('background-position',
//    [
//        'background-position-x background-position-y',
//        'background-position-y background-position-x'
//    ], [
//        ['background-position-x',
//            ['type' => 'background-position-x', 'multiple' => true, 'separator' => ',', 'optional' => true]
//        ],
//        ['background-position-y',
//            ['type' => 'background-position-y', 'multiple' => true, 'separator' => ',', 'optional' => true]
//        ]
//    ], ',', false));

$config['properties'] = array_merge($config['properties'], makePropertySet('margin', ['unit unit unit unit'], [
    ['margin-top', 'unit'],
    ['margin-right', 'unit'],
    ['margin-bottom', 'unit'],
    ['margin-left', 'unit']
]));

$config['properties'] = array_merge($config['properties'], makePropertySet('padding', ['unit unit unit unit'], [
    ['padding-top', 'unit'],
    ['padding-right', 'unit'],
    ['padding-bottom', 'unit'],
    ['padding-left', 'unit']
]));

$config['properties'] = array_merge($config['properties'], makePropertySet('border-radius', ['unit unit unit unit'], [
    ['border-top-left-radius', 'unit', ' '],
    ['border-top-right-radius', 'unit', ' '],
    ['border-bottom-right-radius', 'unit', ' '],
    ['border-bottom-left-radius', 'unit', ' ']
], '/'));

$config['alias'] = array_merge($config['alias'], addAlias('-moz-border-radius',
    ['-moz-border-radius' => [
        'alias' => 'border-radius',
        'shorthand' => '-moz-border-radius',
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
        'shorthand' => '-webkit-border-radius',
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

// generate configuration -------------
foreach ($config['map'] as $key => $value) {

    unset($config['map'][$key]['value_map']);
}

foreach ($config['alias'] as $alias => $data) {

    $properties = $config['properties'][$data['alias']];

    if (isset($properties['value_map'])) {

        $map = [];
        $j = count($properties['properties']);

        while (--$j > 0) {

            $map[$data['properties'][$j]] = $properties['value_map'][$properties['properties'][$j]];
        }

        $properties['value_map'] = $map;
    }

    if (isset($data['properties'])) {

        $properties['properties'] = $data['properties'];
    }

    if (isset($data['shorthand'])) {

        $properties['shorthand'] = $data['shorthand'];
    }

    $config['properties'][$alias] = $properties;
}

unset($config['alias']);

$file = dirname(__DIR__) . '/src/TBela/CSS/config.json';
file_put_contents($file, json_encode($config));

echo "the configuration has been stored in '$file' ...\n";


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
 * @param array $pattern
 * @param array $props
 * @param null|string $separator
 * @param bool $map_properties
 * @param array|null $settings
 * @param string|null
 * @return array
 */
function makePropertySet(string $shorthand, array $pattern, array $props, ?string $separator = null, bool $map_properties = true, ?array $settings = null, $shorthandOverride = null): array
{

    $properties = [];
    foreach ($props as $key => $prop) {

        // properties definition
        if (is_string($prop[1])) {

            $properties[$prop[0]] = ['type' => $prop[1]];
        } else {

            $properties[$prop[0]] = $prop[1];
        }

        // some properties can be omitted if they match each other
        // example margin-top and margin-right: margin: 5px 5px -> margin: 5px
        if ($map_properties) {

            if ($key > 0 && $key < 3) {

                $properties[$prop[0]]['value_map'] = [$props[0][0]];
            }

            if ($key == 3) {

                $properties[$prop[0]]['value_map'] = [$props[1][0], $props[0][0]];
            }
        }

        if (isset($props[1][2])) {

            $properties[$prop[0]]['separator'] = $props[1][2];
        }
    }

    if (!is_null($settings)) {

        $properties[$shorthand . '.settings'] = $settings;
    }

    return Config::addSet($shorthand, $pattern, $properties, $separator, $shorthandOverride);
}