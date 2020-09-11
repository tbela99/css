<?php

namespace TBela\CSS\Query;

use InvalidArgumentException;
use TBela\CSS\Value;

class TokenSelectorValueAttributeFunctionBeginswith implements TokenSelectorValueInterface
{
    /**
     * @var TokenSelectorValueAttributeExpression
     */
    protected TokenSelectorValueAttributeExpression $expression;

    /**
     * TokenSelectorValueAttributeExpression constructor.
     * @param \stdClass $value
     */
    public function __construct($value)
    {
        $this->expression = new TokenSelectorValueAttributeExpression($value->arguments);
    }

    /**
     * @inheritDoc
     */
    public function evaluate(array $context): array {

       return $this->expression->evaluate($context);
    }
}