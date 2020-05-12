<?php

namespace TBela\CSS\Query;

use TBela\CSS\Element\AtRule;
use TBela\CSS\Element\Rule;

/**
 * Evaludate node name
 * @package TBela\CSS\Query
 */
class TokenSelectorValueString extends TokenSelectorValue
{
    protected string $value = '';
    protected bool $isAtRule = false;

    public function __construct($data)
    {
        parent::__construct($data);

        $this->isAtRule = substr($this->value, 0, 1) == '@';
    }

    /**
     * @inheritDoc
     */
    public function evaluate(array $context): array
    {
        $result = [];

        foreach ($context as $element) {

            if ($this->isAtRule && !($element instanceof AtRule)) {

                continue;
            }

            if ($element instanceof Rule) {

                $name = implode(',', $element->getSelector());

            }
            else {

                $name = ($element instanceof AtRule ? '@' : '').$element['name'];
            }

            if($this->value === $name) {

                $result[] = $element;
            }
        }

        return $result;
    }
}