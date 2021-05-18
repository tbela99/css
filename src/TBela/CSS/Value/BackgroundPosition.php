<?php

namespace TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class BackgroundPosition extends ShortHand
{

    public static array $patterns = [
        [

            [
                'type' => 'background-position-left', 'optional' => true
            ],

            [
                'type' => 'background-position-left', 'optional' => true
            ],
            [
                'type' => 'background-position-top', 'optional' => true
            ],
            [
                'type' => 'background-position-top', 'optional' => true
            ]
        ]
    ];

    protected static array $defaults = ['0 0'];
}
