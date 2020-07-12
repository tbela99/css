<?php

namespace TBela\CSS\Query;

interface TokenSelectorValueInterface
{

    /**
     * @param QueryInterface[] $context
     * @return bool
     */
    public function evaluate(array $context);
}