<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

/**
 * CSS function value
 * @package TBela\CSS\Value
 */
class CssAttribute extends Value {

    protected static function validate($data): bool {

        return isset($data->arguments) && is_array($data->arguments);
    }

    public static function doRender(object $data, array $options = []) {

        return '['. Value::renderTokens($data->arguments, $options).']';
    }
}
