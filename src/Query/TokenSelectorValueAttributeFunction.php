<?php


namespace TBela\CSS\Query;


use InvalidArgumentException;

class TokenSelectorValueAttributeFunction extends TokenSelectorValue implements TokenSelectorValueInterface
{
    protected array $arguments = [];
    protected string $name;
    protected TokenSelectorValueInterface $expression;

    /**
     * TokenSelectorValueAttributeExpression constructor.
     * @param array $value
     */
    public function __construct($value)
    {
        parent::__construct($value);

        $this->arguments = [];

        if ($this->name == 'contains' && count($value->arguments) == 3 && $value->arguments[1]->type == 'separator' && $value->arguments[1]->value == ',') {

            // use TokenSelectorValueAttributeExpression
            $value->arguments[1] = (object) ['type' => 'operator', 'value' => '*='];
            $this->expression = new TokenSelectorValueAttributeExpression($value->arguments);
        }

        else {

            $this->expression = call_user_func([static::class, 'getInstance'], (object) ['type' => $value->name, 'arguments' => $value->arguments]);
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