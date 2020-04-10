<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class FontFamily extends Value
{
    /**
     * @inheritDoc
     */
    public function matchToken ($token, $previousToken = null, $previousValue = null) {

        return $token->type == 'css-string' || $token->type == static::type();
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected static function doParse($string, $capture_whitespace = true)
    {

        $type = static::type();
        $tokens = static::getTokens($string, $capture_whitespace);

        foreach ($tokens as $token) {

            if (static::matchToken($token)) {

                if ($token->type == 'css-string') {

                    $token->value = static::stripQuotes($token->value);
                }

                $token->type = $type;
            }
        }

        return new Set(static::reduce($tokens));
    }
}
