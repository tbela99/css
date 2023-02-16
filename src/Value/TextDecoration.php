<?php

namespace TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class TextDecoration extends ShortHand
{

    public static array $keywords = ['none'];

    /**
     * @var array
     * @ignore
     */
    protected static array $patterns = [

        'keyword',
        [
            ['type' => 'text-decoration-line', 'optional' => true],
            ['type' => 'text-decoration-style', 'optional' => true],
            ['type' => 'text-decoration-color', 'optional' => true],
			['type' => 'text-decoration-thickness', 'optional' => true]
        ]
    ];

    /**
     * @inheritDoc
     */
//    public static function matchPattern(array $tokens)
//    {
//
//        $tokens = static::reduce(parent::matchPattern($tokens));
//
//        $result = [];
//
//        for ($i = 0; $i < count($tokens); $i++) {
//
//            if (in_array($tokens[$i]->type, ['separator', 'whitespace'])) {
//
//                $result[] = $tokens[$i];
//                continue;
//            }
//
//            $k = $i;
//            $j = count($tokens);
//            $matches = [$tokens[$i]];
//
//            while (++$k < $j) {
//
//                if ($tokens[$k]->type == 'whitespace') {
//
//                    continue;
//                }
//
//                if ($tokens[$k]->type != $tokens[$i]->type) {
//
//                    $k = $k - 1;
//
//                    if (count($matches) == 1) {
//
//                        array_splice($result, count($result), 0, array_slice($tokens, $i, $k - $i + 1));
//                        $i = $k;
//                        continue 2;
//                    }
//
//                    break;
//                } else {
//
//                    $matches[] = $tokens[$k];
//                }
//            }
//
//            $slice = array_slice($tokens, $i, $k - $i + 1);
//            $className = static::getClassName($slice[0]->type);
//            $keyword = $className::matchKeyword(Value::renderTokens($slice));
//
//            if (!is_null($keyword)) {
//
//                $result[] = (object)['type' => $tokens[$i]->type, 'value' => $keyword];
//            } else {
//
//                array_splice($result, count($result), 0, array_slice($tokens, $i, $k - $i + 1));
//            }
//
//            $i = $k;
//        }
//
//        return $result;
//    }
}
