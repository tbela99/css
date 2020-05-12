<?php

namespace TBela\CSS\Query;

use TBela\CSS\Element\AtRule;
use TBela\CSS\Element\Rule;

class TokenSelectorValueAttribute extends TokenSelectorValue
{
    protected array $value = [];
    protected TokenSelectorValueInterface $expression;

    public function __construct($data)
    {
        parent::__construct($data);

        if (count($data->value) == 3) {

            $this->expression = new TokenSelectorValueAttributeExpression($data->value);
        }

        else if (count($data->value) == 1) {

            $this->expression = call_user_func([TokenSelectorValueAttribute::class, 'getInstance'], $data->value[0]);
        }

        else {

            throw new \Exception(sprintf('attribute not implemented %s', var_export($data, true)), 501);
        }
    }

    /**
     * @inheritDoc
     */
    public function evaluate(array $context): array
    {

        return $this->expression->evaluate($context);
    }
}