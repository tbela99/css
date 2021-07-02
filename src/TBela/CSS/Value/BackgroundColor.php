<?php

namespace TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class BackgroundColor extends Color
{

    public static array $defaults = ['transparent', '#0000'];

    public static function doParse(string $string, bool $capture_whitespace = true, $context = '', $contextName = ''): Set
    {
        $tokens = [];

        foreach (parent::getTokens($string, $capture_whitespace, $context, $contextName) as $token) {

            if ($token->type == 'color') {

                $token->type = static::type();
            }

            $tokens[] = $token;
        }

        return new Set(static::reduce($tokens));
    }

    public static function matchToken($token, $previousToken = null, $previousValue = null, $nextToken = null, $nextValue = null, int $index = null, array $tokens = []): bool
    {
        return $token->type == 'color';
    }
}
