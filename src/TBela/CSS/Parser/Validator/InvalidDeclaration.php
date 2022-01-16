<?php

namespace TBela\CSS\Parser\Validator;

use TBela\CSS\Interfaces\ValidatorInterface;
use TBela\CSS\Parser\SyntaxError;

class InvalidDeclaration implements ValidatorInterface
{
    use ValidatorTrait;

    public function validate(object $token, object $parentRule, object $parentStylesheet): int
    {

        $this->error = 'malformed declaration';

        return static::REJECT;
    }
}