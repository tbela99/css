<?php

namespace TBela\CSS\Value;

use \Exception;
use stdClass;
use \TBela\CSS\Value;

// pattern font-style font-variant font-weight font-stretch font-size / line-height <'font-family'>

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
