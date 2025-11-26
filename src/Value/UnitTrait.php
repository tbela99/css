<?php

namespace TBela\CSS\Value;

/**
 * parse font
 * @package TBela\CSS\Value
 */
trait UnitTrait
{

    /**
     * @inheritDoc
     */

    public static function doRender(object $data, array $options = []) {

        $value = $data->value;

        if ($value == '0') {

            return '0';
        }

        if (!empty($options['compress']) && is_numeric($value)) {

            $value = Number::compress($value);
        }

        if (isset($data->unit)) {

            return $value . $data->unit;
        }

        return $value;
    }
}
