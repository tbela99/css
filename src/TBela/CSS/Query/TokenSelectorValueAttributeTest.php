<?php

namespace TBela\CSS\Query;

class TokenSelectorValueAttributeTest extends TokenSelectorValueAttribute
{
    protected $name;

    /**
     * TokenSelectorValueAttributeTest constructor.
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function evaluate(array $context)
    {
        $result = [];

        foreach ($context as $element) {

            if (!is_null($element[$this->name])) {

                $result[] = $element;
            }
        }

        return $result;
    }
}