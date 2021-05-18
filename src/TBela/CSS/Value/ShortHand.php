<?php

namespace TBela\CSS\Value;

use \Exception;
use TBela\CSS\Property\Config;
use \TBela\CSS\Value;

/**
 * parse shorthand
 * @package TBela\CSS\Value
 */
class ShortHand extends Value
{
    /**
     * @var array
     * @ignore
     */
    protected static array $patterns = [

        /*
        'keyword',
        [
            ['type' => 'font-weight', 'optional' => true],
            ['type' => 'font-style', 'optional' => true],
            ['type' => 'font-variant', 'optional' => true, 'match' => 'keyword', 'keywords' => ['normal', 'small-caps']],
            ['type' => 'font-stretch', 'optional' => true],
            ['type' => 'font-size'],
            ['type' => 'line-height', 'optional' => true, 'prefix' => '/', 'previous' => 'font-size'],
            ['type' => 'font-family', 'multiple' => true, 'separator' => ',']
        ]
        */
    ];

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected static function doParse(string $string, bool $capture_whitespace = true, $context = '', $contextName = ''): Set
    {

        $keyword = static::matchKeyword($string);

        if (!is_null($keyword)) {

            return new Set([(object)['value' => $keyword, 'type' => static::type()]]);
        }

        $separator = Config::getPath('map.'.static::type().'.separator');
        $tokens = static::getTokens($string, $capture_whitespace, $context, $contextName);

        if (is_null($separator)) {

           return new Set(static::reduce(static::matchPattern($tokens)));
        }

        $sets = [];
        $values = [];

        foreach ($tokens as $token) {

            if (isset($token->value) && $token->value == $separator) {

                if (!empty($values)) {

                    $sets[] = $values;
                }

                $values = [];
            }

            else {

                $values[] = $token;
            }
        }

        if (!empty($values)) {

            $sets[] = $values;
            unset($values);
        }

        $result = [];

        foreach ($sets as $values) {

            $keyword = static::matchKeyword(implode(' ', array_map(Value::class.'::getInstance', $values)));

            if (!is_null($keyword)) {

                $result[] = Value::getInstance((object)['value' => $keyword, 'type' => static::type()]);
            }

            else {

                array_splice($result, count($result), 0,
                    static::matchPattern($values));
            }

            $result[] = Value::getInstance((object) ['value' => $separator, 'type' => 'separator']);
        }

        $end = end($result);

        if (!is_null($end) && $end->type == 'separator' && $end->value == $separator) {

            array_pop($result);
        }

        return new Set(static::reduce($result));





//        $sets = [];
//        $set = new Set();
//
//        while ($token = array_shift($tokens)) {
//
//            if ($token->type == 'separator' && $token->value == $separator) {
//
//                $filtered = array_values(array_filter($sets, function ($token) {
//
//                    return $token->type != 'whitespace';
//                }));
//
//                if (count($filtered) == 1 && isset($filtered[0]->value)) {
//
//                    $keyword = static::matchKeyword($filtered[0]->value);
//
//                    if (!is_null($keyword)) {
//
//                        $set->add(Value::getInstance((object)['value' => $keyword, 'type' => static::type()]));
//                    }
//                }
//
//                else {
//
//                    $set->merge(new Set(static::reduce(static::matchPattern($sets))));
//                }
//
//                $set->add(Value::getInstance($token));
//                $sets = [];
//            }
//            else {
//
//                $sets[] = $token;
//            }
//        }
//
//        if (!empty($sets)) {
//
//            $set->merge(new Set(static::reduce(static::matchPattern($sets))));
//        }
//
//        return $set;
    }

    public static function matchPattern(array $tokens)
    {

        foreach (static::$patterns as $patterns) {

            if (is_string($patterns)) {

                continue;
            }

            $j = count($tokens);
            $previous = null;
            $next = null;

            for ($i = 0; $i < $j; $i++) {

                if (!isset($tokens[$i]->type)) {

                    echo new Exception();
                }

                if (in_array($tokens[$i]->type, ['separator', 'whitespace'])) {

                    continue;
                }

                // is this a valid font definition?
                foreach ($patterns as $key => $pattern) {

                    $className = static::getClassName($pattern['type']) . '::matchToken';

                    $k = $i + 1;
                    $next = $tokens[$k] ?? null;

                    while (!is_null($next)) {

                        if (!in_array($next->type, ['separator', 'whitespace'])) {

                            break;
                        }

                        $next = $tokens[++$k] ?? null;
                    }

                    if (call_user_func($className, $tokens[$i], $tokens[$i - 1] ?? null, $previous, $tokens[$i + 1] ?? null, $next)) {

                        $tokens[$i]->type = $pattern['type'];
                        $previous = $tokens[$i];

                        if (!empty($pattern['multiple'])) {

                            while (++$i < $j) {

                                if (in_array($tokens[$i]->type, ['separator', 'white-space'])) {

                                    continue;
                                }

                                if (call_user_func($className, $tokens[$i], $tokens[$i - 1], $previous)) {

                                    $tokens[$i]->type = $pattern['type'];
                                }
                            }

                            $previous = $tokens[$i - 1];
                        }

                        unset($patterns[$key]);
                        break;
                    } // failure to match a mandatory property
                    else if (empty($pattern['optional'])) {

                        break;
                    }
                }
            }

            $mandatory = array_values(array_filter($patterns, function ($pattern) {

                return empty($pattern['optional']);
            }));
//
            if (!empty($mandatory)) {

                throw new Exception(' Invalid "' . static::type() . '" definition, missing \'' . $mandatory[0]['type'] . '\' in "'.implode(' ', array_map(Value::class.'::getInstance', $tokens)).'"', 400);
            }

            $i = count($tokens);

            while ($i--) {

                if (call_user_func(static::getClassName($tokens[$i]->type) . '::matchDefaults', $tokens[$i])) {

                    array_splice($tokens, $i, 1);
                }
            }
        }

        return $tokens;
    }

    public function getHash()
    {

        if (is_null($this->hash)) {

            $this->hash = $this->render(['compress' => true]);
        }

        return $this->hash;
    }
}
