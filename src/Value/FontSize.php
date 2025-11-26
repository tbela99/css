<?php

namespace TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class FontSize extends Unit
{

    use UnitTrait, ValueTrait;

    protected static array $keywords = [
        'xx-small',
        'x-small',
        'small',
        'medium',
        'large',
        'x-large',
        'xx-large',
        'xxx-large',
        'larger',
        'smaller'
    ];

    protected static array $defaults = ['medium'];

    /**
     * @inheritDoc
     */
    public static function matchToken($token, $previousToken = null, $previousValue = null, $nextToken = null, $nextValue = null, ?int $index = null, array $tokens = []): bool
    {
        if (($token->type == 'number' && $token->value == 0) || ($token->type == 'unit' && !in_array($token->unit, ['turn', 'rad', 'grad', 'deg']))) {

            return true;
        }

        if ($token->type == 'css-string' && in_array(strtolower($token->value), static::$keywords)) {

            return true;
        }

        return $token->type == static::type();
    }
}
