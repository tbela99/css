<?php

namespace TBela\CSS\Value;

/**
 * parse font
 * @package TBela\CSS\Value
 */
class Outline extends ShortHand
{
    /**
     * @var array
     * @ignore
     */
    protected static array $patterns = [

        [
            ['type' => 'outline-style', 'optional' => true],
            ['type' => 'outline-width', 'optional' => true],
            ['type' => 'outline-color', 'optional' => true]
        ]
    ];
}
