<?php

namespace TBela\CSS\Value;

use TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class BackgroundSize extends Value
{

    use UnitTrait, ValueTrait;

    protected static array $keywords = [
        'contain',
        'auto',
        'cover'
    ];

    protected static array $defaults = ['auto', 'auto auto'];

    /**
     * @var array
     * @ignore
     */
    protected static array $patterns = [

        'keyword',
        [
            ['type' => 'unit'],
            ['type' => 'unit', 'optional' => true]
        ]
    ];

    public static function matchKeyword(string $string, array $keywords = null): ?string
    {
        $string = trim($string, ";\n\t\r ");
        $string = preg_replace('#\s+#', ' ', $string);

        if ($string == 'auto auto') {

            $string = 'auto';
        }

        return parent::matchKeyword($string, $keywords);
    }

    public static function matchToken($token, $previousToken = null, $previousValue = null, $nextToken = null, $nextValue = null, int $index = null, array $tokens = []): bool
    {

        return $token->type == 'unit' || ($token->type == 'css-string' && in_array($token->value, static::$keywords));
    }

    public static function reduce(array $tokens, array $options = []): array
    {
        $tokens = parent::reduce($tokens, array_merge($options, ['remove_defaults' => false]));

        if (count($tokens) == 3 && static::matchDefaults($tokens[2]) && $tokens[1]->type == 'whitespace') {

            array_splice($tokens, 1, 2);
        }

        return $tokens;
    }
}
