<?php

namespace TBela\CSS\Value;

use TBela\CSS\ArrayTrait;
use TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class BackgroundPositionTop extends BackgroundPositionLeft
{

    use ArrayTrait;

    protected static array $previous = ['background-position-top'];
    public static array $keywords = ['top', 'center', 'bottom'];
}
