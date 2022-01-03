<?php

namespace TBela\CSS\Parser\Validator;

use TBela\CSS\Interfaces\ValidatorInterface;
use TBela\CSS\Value;

class AtRule implements ValidatorInterface
{
    public function validate(object $token, object $parentRule, object $parentStylesheet): int
    {

       if(in_array($parentRule->type, ['Rule', 'AtRule', 'NestingRule', 'NestingMediaRule', 'Stylesheet'])) {

           return static::VALID;
       }

        return static::VALID;
    }
}