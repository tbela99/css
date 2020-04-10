<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class FontVariant extends Value
{

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

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected static function doParse($string, $capture_whitespace = true)
    {

        $type = static::type();
        $tokens = static::getTokens($string, $capture_whitespace);

        foreach ($tokens as $key => $token) {

            if (static::matchToken($token)) {

                $token->type = $type;
            }
        }

        return new Set(static::reduce($tokens));
    }
}
