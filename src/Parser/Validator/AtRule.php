<?php

namespace TBela\CSS\Parser\Validator;

use TBela\CSS\Interfaces\ValidatorInterface;
use TBela\CSS\Parser\SyntaxError;

class AtRule implements ValidatorInterface
{
    use ValidatorTrait;

    public function validate(object $token, object $parentRule, object $parentStylesheet): int
    {

        if ($token->name == 'charset') {

            if (!empty($parentRule->children) ||
                $token->location->start->index != 0) {

                $this->error = '@charset must be at the beginning of the document';
                return static::REJECT;
            }

            $firstChar = substr($token->value[0]->value ?? '', 0, 1);

            if ($firstChar != '"') {

                $this->error = sprintf("@% '%s' expected but '%s' found (%s)", $token->name, '"', $firstChar, $token->value[0]->value);
                return static::REJECT;
            }
        }

       if($token->name == 'import') {

           $children = $parentRule->children ?? [];
           $i = count($children);

           while ($i--) {

               if ($children[$i]->type == 'Comment') {

                   continue;
               }

               if ($children[$i]->type != 'AtRule' || !in_array($children[$i]->name, ['charset', 'import'])) {

                   $this->error = '@import rule must follow @charset or @import or it must be the first rule';
                   return static::REJECT;
               }

               break;
           }
       }

        return static::VALID;
    }
}