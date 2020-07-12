<?php

namespace TBela\CSS\Query;

interface TokenInterface
{

    /**
     * @param QueryInterface[] $context
     * @return QueryInterface[]
     */
    public function filter(array $context);
}