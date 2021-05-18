<?php

namespace TBela\CSS\Value;

use TBela\CSS\ArrayTrait;
use TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class Background extends ShortHand
{

    public static array $keywords = ['none'];

    /**
     * @var array
     * @ignore
     */
    protected static array $patterns = [

        'keyword',
        [
            ['type' => 'background-size', 'multiple' => true, 'optional' => true, 'prefix' => '/'],
            ['type' => 'background-color', 'optional' => true],
            ['type' => 'background-image', 'optional' => true],
            ['type' => 'background-attachment', 'optional' => true],
            ['type' => 'background-clip', 'optional' => true],
            ['type' => 'background-origin', 'multiple' => true, 'optional' => true],
            ['type' => 'background-position', 'multiple' => true, 'optional' => true],
            ['type' => 'background-repeat', 'multiple' => true, 'optional' => true]
        ]
    ];
}
