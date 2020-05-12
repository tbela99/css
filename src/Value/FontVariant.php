<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class FontVariant extends Value
{

    use ValueTrait;
    protected static $keywords = [
        'normal',
        'none',
        'small-caps',
        'all-small-caps',
        'petite-caps',
        'all-petite-caps',
        'unicase',
        'titling-caps'
    ];

    protected static $defaults = ['normal'];

    /**
     * @inheritDoc
     */
    public function matchToken($token, $previousToken = null, $previousValue = null)
    {

        if ($token->type == 'css-string' && in_array(strtolower($token->value), static::$keywords)) {

            return true;
        }

        return $token->type == static::type();
    }
}