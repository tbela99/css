<?php


namespace TBela\CSS\Query;


use InvalidArgumentException;

class TokenSelectorValueAttributeIndex implements TokenSelectorValueInterface
{
    protected int $value;

    /**
     * TokenSelectorValueAttributeExpression constructor.
     * @param $value
     */
    public function __construct($value)
    {
        $this->value = $value->value;
    }

    /**
     * @inheritDoc
     */
    public function evaluate(array $context): array
    {
        return isset($context[$this->value - 1]) ? [$context[$this->value - 1]] : [];
    }
}