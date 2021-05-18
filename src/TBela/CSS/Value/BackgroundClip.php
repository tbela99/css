<?php

namespace TBela\CSS\Value;

use TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class BackgroundClip extends Value
{

    use ValueTrait;

    /**
     * @var string[]
     */
    protected static array $keywords = [
        'border-box',
        'padding-box',
        'content-box',
        'text'
    ];

    protected static array $defaults = ['border-box'];
}
