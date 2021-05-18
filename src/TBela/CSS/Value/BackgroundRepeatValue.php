<?php

namespace TBela\CSS\Value;

use TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class BackgroundRepeatValue extends Value
{

    /**
     * @var string[]
     */
    protected static array $keywords = [
        'repeat',
        'space',
        'round',
        'no-repeat'
    ];
}
