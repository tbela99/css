<?php

namespace TBela\CSS\Value;

/**
 * Css unit value
 * @package TBela\CSS\Value
 */
class Unit extends Number {

    /**
     * @inheritDoc
     */
    protected static function validate($data) {

        return isset($data->unit);
    }

    /**
     * @inheritDoc
     */
    public function match ($type) {

        $dataType = strtolower($this->data->type);
        return $dataType == $type || ($type == 'number' && $this->data->value == 0);
    }

    /**
     * @inheritDoc
     */
    public function render(array $options = [])
    {

        if ($this->data->value == 0) {

            return '0';
        }

        if (!empty($options['compress'])) {

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
