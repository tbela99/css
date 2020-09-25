<?php

namespace TBela\CSS\Query;

use Exception;
use InvalidArgumentException;

trait FilterTrait
{
    public function trim(array $value) {

        $j = count($value);

        while ($j--) {

            if ($value[$j]->type == 'whitespace') {

                array_splice($value, $j, 1);
            }
        }

        return $value;
    }
}