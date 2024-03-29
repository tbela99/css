<?php

namespace TBela\CSS\Parser\Validator;

use TBela\CSS\Interfaces\ValidatorInterface;
use TBela\CSS\Parser\SyntaxError;

class InvalidRule implements ValidatorInterface
{
    use ValidatorTrait;

    public function validate(object $token, object $parentRule, object $parentStylesheet): int
    {

        $this->error = 'malformed rule';

        return static::REJECT;
    }
}