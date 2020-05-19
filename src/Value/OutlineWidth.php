<?php

namespace TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class OutlineWidth extends Unit
{
    use ValueTrait;

    protected static array $keywords = [
        'thin',
        'medium',
        'thick'
    ];

    /**
     * @inheritDoc
     */
    public static function matchToken($token, $previousToken = null, $previousValue = null): bool
    {

        return $token->type == 'unit' || ($token->type == 'number' && $token->value == 0);
    }
}
