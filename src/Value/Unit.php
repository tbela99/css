<?php

namespace TBela\CSS\Value;

class Unit extends Number {

    protected static function validate($data) {

        return isset($data->unit);
    }

    public function render($compressed = false, array $options = [])
    {

        if ($this->data->value == 0) {

            return '0';
        }

        if ($compressed) {

            $value = $this->data->value;
            $unit = $this->data->unit;

            if ($this->data->unit == 'ms' && $value >= 100) {

                $unit = 's';
                $value /= 1000;
            }

            return $this->compress($value).$unit;
        }

        return $this->data->value.$this->data->unit;
    }
}
