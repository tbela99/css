<?php

namespace TBela\CSS\Value;

use \Exception;
use stdClass;
use \TBela\CSS\Value;

// pattern font-style font-variant font-weight font-stretch font-size / line-height <'font-family'>

/**
 * parse font
 * @package TBela\CSS\Value
 */
trait ValueTrait
{

    /**
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
