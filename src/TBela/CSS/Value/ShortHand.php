<?php

namespace TBela\CSS\Value;

use \Exception;
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
    protected static function doParse(string $string, bool $capture_whitespace = true): Set
    {

        $keyword = static::matchKeyword($string);

        if (!is_null($keyword)) {

            return new Set([(object) ['value' => $keyword, 'type' => static::type()]]);
        }

        $tokens = static::getTokens($string, $capture_whitespace);

        foreach (static::$patterns as $patterns) {

            if ($patterns == 'keyword') {

                $quote = $string[0];
                $keyword = $string;

                if (in_array($quote, ['"', "'"]) && substr($string, -1) == $quote) {

                    $keyword = substr($string, 1, -1);
                }

                foreach (static::$keywords as $word) {

                    if (strcasecmp($keyword, $word) === 0) {

                        $tokens[0]->type = static::type();
                        return new Set($tokens);
                    }
                }
            } else {

                $j = count($tokens);
                $previous = null;

                for ($i = 0; $i < $j; $i++) {

                    if (in_array($tokens[$i]->type, ['separator', 'whitespace'])) {

                        continue;
                    }

                    // is this a valid font definition?
                    foreach ($patterns as $key => $pattern) {

                        $className = static::getClassName($pattern['type']) . '::matchToken';

                        if (call_user_func($className, $tokens[$i], $tokens[$i - 1] ?? null, $previous)) {

                            array_splice($patterns, $key, 1);

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

                if (!empty($mandatory)) {

                    throw new Exception(' Invalid "'.static::type().'" definition, missing \'' . $mandatory[0]['type'] . '\' definition in "' . $string . '"', 400);
                }

                $i = count($tokens);

                while ($i--) {

                    if (call_user_func(static::getClassName($tokens[$i]->type) . '::matchDefaults', $tokens[$i])) {

                        array_splice($tokens, $i, 1);
                    }
                }
            }
        }

        return new Set(static::reduce($tokens));
    }
}
