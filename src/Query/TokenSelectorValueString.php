<?php

namespace TBela\CSS\Query;

use TBela\CSS\Element\AtRule;
use TBela\CSS\Element\Rule;
use TBela\CSS\Value;

/**
 * Evaluate node name
 * @package TBela\CSS\Query
 */
class TokenSelectorValueString extends TokenSelectorValue
{
    use TokenStringifiableTrait;

    protected string $q = '';
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
        if ($this->value == '*') {

            return $context;
        }

        $result = [];

        foreach ($context as $element) {

            if ($this->isAtRule && !($element instanceof AtRule)) {

                continue;
            }

            if ($element instanceof Rule) {

                $value = preg_quote($this->value, '#');

                foreach ($element->getSelector() as $selector) {

                    if (preg_match('#(\s|^)' . $value . '(\s|$)#', $selector)) {

                        $result[] = $element;
                        continue 2;
                    }
                }
            } else {

                $name = ($element instanceof AtRule ? '@' : '') . $element['name'];

                if ($this->value === $name) {

                    $result[] = $element;
                }
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function render(array $options = [])
    {

        return $this->q . $this->value . $this->q;
    }
}