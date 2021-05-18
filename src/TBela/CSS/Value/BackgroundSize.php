<?php

namespace TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class BackgroundSize extends ShortHand
{

    protected static array $keywords = [
        'contain',
        'auto',
        'cover'
    ];

    protected static array $defaults = ['auto auto'];

    /**
     * @var array
     * @ignore
     */
    protected static array $patterns = [

        'keyword',
        [
            ['type' => 'background-size-width'],
            ['type' => 'background-size-height', 'optional' => true]
        ]
    ];
}
