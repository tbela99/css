<?php

namespace TBela\CSS\Parser\Validator;

use TBela\CSS\Interfaces\ValidatorInterface;
use TBela\CSS\Value;

class NestingAtRule implements ValidatorInterface
{
    public function validate(object $token, object $parentRule, object $parentStylesheet): int
    {

       if(!in_array($parentRule->type, ['Rule', 'NestingRule', 'NestingAtRule', 'NestingMediaRule']) &&
           !in_array($parentStylesheet->type, ['Rule', 'NestingRule', 'NestingAtRule', 'NestingMediaRule'])) {

           return static::REJECT;
       }

        foreach (Value::split($token->selector, ',') as $selector) {

            if (strpos($selector, '&') === false) {

                return static::REJECT;
            }
        }

        return static::VALID;
    }
}