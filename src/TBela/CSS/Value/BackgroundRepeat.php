<?php

namespace TBela\CSS\Value;

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

        $key = preg_replace('~(\s+)~', ' ', trim($string));

        if (isset(static::$keymap[$key])) {

            return static::$keymap[$key];
        }

        return parent::matchKeyword($string, $keywords);
    }
}
