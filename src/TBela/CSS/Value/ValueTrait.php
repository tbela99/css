<?php

namespace TBela\CSS\Value;

// pattern font-style font-variant font-weight font-stretch font-size / line-height <'font-family'>

/**
 * parse font
 * @package TBela\CSS\Value
 */
trait ValueTrait
{

    /**
     * @param string $string
     * @param bool $capture_whitespace
     * @return Set
     */
    protected static function doParse(string $string, bool $capture_whitespace = true, $context = ''): Set
    {

        $type = static::type();
        $tokens = static::getTokens($string, $capture_whitespace, $context);

        foreach ($tokens as $key => $token) {

            if (static::matchToken($token)) {

                $token->type = $type;
            }
        }

        return new Set(static::reduce($tokens));
    }
}
