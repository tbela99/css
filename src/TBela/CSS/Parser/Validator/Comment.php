<?php

namespace TBela\CSS\Parser\Validator;

use TBela\CSS\Interfaces\ValidatorInterface;

class Comment implements ValidatorInterface
{
    public function validate(object $token, object $parentRule, object $parentStylesheet): int
    {

        if (substr($token->value, 0, 4) == '<!--') {

            if ($parentRule->type !== 'Stylesheet') {

                return static::REJECT;
            }

            return static::REMOVE;
        }

        return substr($token->value, 0, 3) == '/*#' ? static::REMOVE : static::VALID;
    }
}