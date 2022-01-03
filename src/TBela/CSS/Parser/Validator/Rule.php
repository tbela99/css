<?php

namespace TBela\CSS\Parser\Validator;

use TBela\CSS\Interfaces\ValidatorInterface;
use TBela\CSS\Value;

class Rule implements ValidatorInterface
{
    public function validate(object $token, object $parentRule, object $parentStylesheet): int
    {

       if(in_array($parentRule->type, ['NestingAtRule', 'NestingRule', 'Rule']) ||
           in_array($parentStylesheet->type, ['NestingAtRule', 'NestingRule', 'Rule'])) {

           foreach (Value::split($token->selector, ',') as $selector) {

               if (strpos(trim($selector), '&') !== 0) {

//                   var_dump(
//                       sprintf('(rejected "%s") '.$parentRule->type.' -> '.$token->type.' '.$token->selector, $selector));
                   return static::REJECT;
               }
           }

//           return static::VALID;
       }

//       else {

//           var_dump(sprintf('(context %s:%s \ %s) ', $parentStylesheet->type, $parentStylesheet->name ?? $parentStylesheet->selector, $parentRule->type).' -> '.$token->type.' '.$token->selector);

//       }

        return static::VALID;
    }
}