<?php

namespace TBela\CSS\Value;

use TBela\CSS\Property\Config;
use TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class BackgroundRepeat extends Value
{

    use ValueTrait;

    /**
     * @var string[]
     */
    protected static array $keywords = [
        'repeat-x',
        'repeat-y',
        'repeat',
        'space',
        'round',
        'no-repeat'
    ];

    /**
     * @var string[]
     */
    protected static array $keymap = [
        'repeat no-repeat' => 'repeat-x',
        'no-repeat repeat' => 'repeat-y',
        'repeat repeat' => 'repeat',
        'space space' => 'space',
        'round round' => 'round',
        'no-repeat no-repeat' => 'no-repeat'
    ];

    protected static array $patterns = [
        'keyword',
        [
            ['type' => 'background-repeat'],
            ['type' => 'background-repeat', 'optional' => true]
        ]
    ];

    /**
     * @var string[]
     */
    protected static array $defaults = ['repeat'];

    public static function matchKeyword(string $string, array $keywords = null): ?string
    {

        $key = preg_replace('~(\s+)~', ' ', trim($string, ";\n\t\r "));

        if (isset(static::$keymap[$key])) {

            return static::$keymap[$key];
        }

        return parent::matchKeyword($string, $keywords);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    protected static function doParse($string, $capture_whitespace = true, $context = '', $contextName = '')
    {

        $type = static::type();

        $separator = Config::getProperty($type.'.separator');

        if (is_null($separator)) {

            $matchKeyword = static::matchKeyword($string);

            if (!is_null($matchKeyword)) {

                return [(object)['type' => $type, 'value' => $matchKeyword]];
            }
        }

        else {

            $strings = array_map(function ($token) {

                return static::matchKeyword($token) ?? trim($token, ";\n\t\r ");
            }, Value::split($string, $separator));

            $string = implode(',', $strings);
        }

        $tokens = static::getTokens($string, $capture_whitespace, $context, $contextName);

        foreach ($tokens as $token) {

            if (static::matchToken($token)) {

                if ($token->type == 'css-string') {

                    $value = static::matchKeyword($token->value);

                    if (!is_null($value)) {

                        $token->value = $value;
                    }
                }

                $token->type = $type;
            }
        }

        return static::reduce($tokens);
    }
}
