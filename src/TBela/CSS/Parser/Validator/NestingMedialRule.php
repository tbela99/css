<?php

namespace TBela\CSS\Parser\Validator;

use TBela\CSS\Interfaces\ValidatorInterface;
use TBela\CSS\Value;

class NestingMedialRule implements ValidatorInterface
{
    use  ValidatorTrait;
    
    public function validate(object $token, object $parentRule, object $parentStylesheet): int
    {

        return static::VALID;
    }
}