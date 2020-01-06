<?php

namespace TBela\CSS\Value;

use TBela\CSS\Value;


class Number extends Value
{

    public function match ($type) {

        return ($this->data->value == '0' && $type == 'unit') || $this->data->type == $type;
    }

    protected static function validate($data)
    {

        return isset($data->value) && is_numeric($data->value) && $data->value !== '';
    }

    public static function compress($value)
    {

        $value = explode('.', (float)$value);

        if (isset($value[1]) && $value[1] == 0) {

            unset($value[1]);
        }

        if (isset($value[1])) {

            // convert 0.20 to .2
            $value[1] = rtrim($value[1], '0');

            if ($value[0] == 0) {

                $value[0] = '';
            }

        } else {

            // convert 1000 to 1e3
            $value[0] = preg_replace_callback('#(0{3,})$#', function ($matches) {

                return 'e' . strlen($matches[1]);
            }, $value[0]);
        }

        return implode('.', $value);
    }

    public function render($compressed = false, array $options = [])
    {

        if ($compressed) {

            return $this->compress($this->data->value);
        }

        return $this->data->value;
    }
}
