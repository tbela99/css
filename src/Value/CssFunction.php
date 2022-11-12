<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

/**
 * CSS function value
 * @package TBela\CSS\Value
 */
class CssFunction extends Value {

    /**
     * @inheritDoc
     */
    protected static function validate($data): bool {

        return isset($data->name) && isset($data->arguments) && is_array($data->arguments);
    }

    /**
     * @inheritDoc
     */
    public static function doRender(object $data, array $options = [])
    {
        return $data->name.'('. Value::renderTokens($data->arguments, $options).')';
    }

    /**
     * @inheritDoc
     */
    public function getValue() {

        return $this->data->arguments->{0}->value;
    }
}
