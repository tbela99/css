<?php

namespace TBela\CSS\Query;

use TBela\CSS\Parser\SyntaxError;

class Evaluator
{
    /**
     * @param string $expression
     * @param QueryInterface $context
     * @return QueryInterface[]
     * @throws SyntaxError
     */
    public function evaluate(string $expression, QueryInterface $context)
    {

        $tokens = (new Parser())->parse($expression);

        if ($tokens === []) {

            return [];
        }

        $result = [$context];
        $j = count($tokens);

        for($i = 0; $i < $j; $i++) {

            $result = $tokens[$i]->filter($result);

            if (empty($result)) {

                break;
            }
        }

        if (count($result) < 2) {

            return $result;
        }

        $info = [];

        /**
         * @var \TBela\CSS\Element $element
         */
        foreach ($result as $key => $element) {

            $index = spl_object_id($element);

            if (!isset($info[$index])) {

                $info[$index] = [
                    'key' => $key,
                    'depth' => [],
                    'name' => is_null($element['name']) ? implode(',', (array) $element['selector']) : $element['name'],
                    'val' => (string) $element
                ];

                $el = $element;

                while ($el && ($parent = $el->getParent())) {

                    $info[$index]['depth'][] = array_search($el, $parent->getChildren(), true);
                    $el = $parent;
                }

                $info[$index]['depth'] = implode('', array_reverse($info[$index]['depth']));
            }
        }

        \usort($info, function ($a, $b) {

            if ($a['depth'] < $b['depth']) {

                return -1;
            }

            if ($a['depth'] > $b['depth']) {

                return 1;
            }

            return 0;
        });

        $res = [];

        foreach ($info as $value) {

            $res[] = $result[$value['key']];
        }

        return $res;
    }
}