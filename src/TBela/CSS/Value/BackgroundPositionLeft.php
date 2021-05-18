<?php

namespace TBela\CSS\Value;

use TBela\CSS\ArrayTrait;
use TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class BackgroundPositionLeft extends Value
{

    use ArrayTrait;

    public static array $keywords = ['left', 'center', 'right'];
    protected static array $previous = ['background-position-left', 'background-position-top'];

    public static function matchToken($token, $previousToken = null, $previousValue = null, $nextToken = null, $nextValue = null): bool
    {

        $test = $token->type == 'unit' &&
            (empty($previousValue) ||
                (isset($previousValue) &&
                    $previousValue->type == static::type() &&
                    in_array($previousValue->value, static::$keywords)));

        if ($token->type == 'unit') {

            if (is_null($nextValue)) {

                return static::type() != 'background-position-left';
            }

            if(isset($previousValue) && $previousValue->type == 'background-position-left') {

//                echo new \Exception();

                if ($nextValue->type == 'unit' || in_array($nextValue->value, BackgroundPositionTop::$keywords)) {

                    return $test;
                }

                return static::type() != 'background-position-left';
            }
        }

       if (in_array($token->value, static::$keywords) &&
           (!isset($previousValue) ||
           !in_array($previousValue->type, static::$previous))) {

           return true;
       }

       return $test;
    }

    public function render(array $options = []): string
    {

        if (isset($this->data->unit)) {

            if ($this->data->value == '0') {

                return '0';
            }

            if (!empty($options['compress'])) {

                return Number::compress($this->data->value).$this->data->unit;
            }

            return $this->data->value.$this->data->unit;
        }

        return $this->data->value;
    }
}
