<?php

namespace TBela\CSS\Query;

use InvalidArgumentException;
use TBela\CSS\Value;

class TokenSelectorValueAttributeFunctionContains implements TokenSelectorValueInterface
{
    /**
     * @var TokenSelectorValueAttributeExpression
     */
    protected $expression;

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
    public function evaluate(array $context) {

       return $this->expression->evaluate($context);
    }
}