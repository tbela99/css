<?php

namespace TBela\CSS\Query;
use TBela\CSS\Parser\SyntaxError;
use function usort;

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

        $tokenList = (new Parser())->parse($expression);

        return $this->sortNodes($tokenList->filter([$context]));

    }

    public function evaluateByClassName(string $classNames, QueryInterface $context)
    {

        $parser = new Parser();

        $selectors = [];

        foreach ($parser->split($classNames) as $className) {

            foreach ($parser->split($className, ',') as $selector) {

                $selector = trim($selector);

                $selectors[$selector] = $selector;
            }
        }

        $selectors = array_values($selectors);

        sort($selectors);

        $result = [];

        $stack = $context->getType() == 'Stylesheet' ? $context->getChildren() : [$context];

        while($node = array_shift($stack)) {

            if ($node->getType() == 'Rule') {

                /**
                 * @var \TBela\CSS\Element\Rule $node
                 */

                if ($this->search($selectors, array_map('trim', $node->getSelector()))) {

                        $result[] = $node;
                }
            }

            /**
             * @var \TBela\CSS\Element\AtRule $node
             */

            else if ($node->getType() == 'AtRule') {

                if ($this->search($selectors, [trim('@'.$node->getName().' '.$node->getValue()->render(['remove_comments' => true]))])) {

                    $result[] = $node;
                }

                if (!$node->isLeaf() && !$node->hasDeclarations()) {

                    array_splice($stack, count($stack), 0, $node->getChildren());
                }
            }
        }

        return $result;
    }

    protected function search(array $selectors, array $search)
    {

        $l = count($search);

        while ($l--) {

            $k = count($selectors) - 1;
            $i = 0;

            while (true) {

                $j = $i + ceil(($k - $i) / 2);

                if ($selectors[$j] < $search[$l]) {

                    if ($i == $j) {

                        return false;
                    }

                    $i = $j;

                } else if ($selectors[$j] > $search[$l]) {

                    if ($k == $j) {

                        return $selectors[$i] === $search[$l];
                    }

                    $k = $j;

                } else if ($selectors[$j] === $search[$l]) {

                    return true;
                }
            }
        }

        return false;
    }

    protected function sortNodes($nodes)
    {

        $info = [];

        /**
         * @var \TBela\CSS\Interfaces\ElementInterface $element
         */
        foreach ($nodes as $key => $element) {

            $index = spl_object_id($element);

            if (!isset($info[$index])) {

                $info[$index] = [
                    'key' => $key,
                    'depth' => [],
                    'name' => is_null($element['name']) ? implode(',', (array)$element['selector']) : $element['name'],
                    'val' => (string)$element
                ];

                $el = $element;

                while ($el && ($parent = $el->getParent())) {

                    $info[$index]['depth'][] = array_search($el, $parent->getChildren(), true);
                    $el = $parent;
                }

                $info[$index]['depth'] = implode('', array_reverse($info[$index]['depth']));
            }
        }

        usort($info, function ($a, $b) {

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

            $res[] = $nodes[$value['key']];
        }

        return $res;
    }
}