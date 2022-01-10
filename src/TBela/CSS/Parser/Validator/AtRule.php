<?php

namespace TBela\CSS\Parser\Validator;

use TBela\CSS\Interfaces\ValidatorInterface;

class AtRule implements ValidatorInterface
{
    public function validate(object $token, object $parentRule, object $parentStylesheet): int
    {

        if ($token->name == 'charset' && (
            !empty($parentRule->children) ||
            $token->location->start->index != 0||
            substr($token->value, 0, 1) != '"')
        ) {

            return static::REJECT;
        }

       if($token->name == 'import') {

           $children = $parentRule->children ?? [];
           $i = count($children);

           while ($i--) {

               if ($children[$i]->type == 'Comment') {

                   continue;
               }

               if ($children[$i]->type != 'AtRule' || !in_array($children[$i]->name, ['charset', 'import'])) {

                   return static::REJECT;
               }

               break;
           }
       }

        return static::VALID;
    }
}