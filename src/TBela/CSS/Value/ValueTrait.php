<?php

namespace TBela\CSS\Value;

// pattern font-style font-variant font-weight font-stretch font-size / line-height <'font-family'>
use TBela\CSS\Property\Config;
use TBela\CSS\Value;

/**
 * parse font
 * @package TBela\CSS\Value
 */
trait ValueTrait
{

    /**
     * @inheritDoc
     */
    protected static function doParse(string $string, bool $capture_whitespace = true, $context = '', $contextName = ''): Set
    {

        $type = static::type();

        $separator = Config::getPath('properties.'.$type.'.separator');

        $strings = is_null($separator) ? [$string] : static::split($string, $separator);

        $result = [];

        foreach ($strings as $string) {

            if (!empty(static::$keywords)) {

                $keyword = static::matchKeyword($string);

                if (!is_null($keyword)) {

                    $result[] = new Set([(object) ['type' => $type, 'value' => $keyword]]);
                    continue;
                }
            }

            $tokens = static::getTokens($string, $capture_whitespace, $context, $contextName);

            foreach ($tokens as $token) {

                if (static::matchToken($token)) {

                    $token->type = $type;
                }
            }

            $result[] = new Set(static::reduce($tokens));
        }

        if (count($result) == 1) {

            return $result[0];
        }

        $i = -1;
        $j = count($result) - 1;

        $set = new Set();

        while (++$i < $j) {

            $set->merge($result[$i])->add(Value::getInstance((object) ['type' => 'separator', 'value' => $separator]));
        }

        $set->merge($result[$j]);

        return $set;
    }
}
