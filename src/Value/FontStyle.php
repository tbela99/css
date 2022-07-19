<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class FontStyle extends Value
{

    use UnitTrait;

    protected static array $keywords = [
        'normal',
        'italic',
        'oblique'
    ];

    protected static array $defaults = ['normal'];


    /**
     * @inheritDoc
     */
    public static function match(object $data, $type): bool
    {

        return strtolower($data->type) == $type;
    }

    /**
     * @inheritDoc
     */
    public static function matchToken ($token, $previousToken = null, $previousValue = null, $nextToken = null, $nextValue = null, int $index = null, array $tokens = []): bool {

        if ($token->type == 'css-string' && in_array(strtolower($token->value), static::$keywords)) {

            return true;
        }

        return $token->type == static::type();
    }
}
