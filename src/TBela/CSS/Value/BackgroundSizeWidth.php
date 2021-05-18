<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class BackgroundSizeWidth extends Unit
{

//    use ValueTrait;
//
    protected static array $keywords = ['auto'];
    protected static array $defaults = [];


    /**
     * @inheritDoc
     */
    public static function matchToken ($token, $previousToken = null, $previousValue = null, $nextToken = null, $nextValue = null): bool {

        if (in_array(strtolower($token->value), static::$keywords) || $token->type == 'unit') {

            return true;
        }

        return $token->type == static::type();
    }
}
