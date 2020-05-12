<?php

namespace TBela\CSS\Query;

use TBela\CSS\Parser\SyntaxError;


class Evaluator
{
    protected array $context = [];

    /**
     * @param $expression
     * @param QueryInterface $context
     * @return QueryInterface[]
     * @throws SyntaxError
     */
    public function evaluate(string $expression, QueryInterface $context)
    {

        $tokens = (new Parser())->parse($expression);

        $result = [$context];
        $j = count($tokens);

        for($i = 0; $i < $j; $i++) {

            $result = $tokens[$i]->filter($result);

            if (empty($result)) {

                break;
            }
        }

        return $result;
    }
}