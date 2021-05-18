<?php

namespace TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class BackgroundImage extends CssFunction
{

    use ValueTrait;

    public static array $keywords = ['none'];

    public static function matchToken($token, $previousToken = null, $previousValue = null, $nextToken = null, $nextValue = null): bool
    {

        return $token->type == static::type() || (isset($token->name) &&
                in_array($token->name, [
                    'url',
                    'linear-gradient',
                    'element',
                    'image',
                    'cross-fade',
                    'image-set'
                ]));
    }
}
