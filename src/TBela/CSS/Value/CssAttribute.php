<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

/**
 * CSS function value
 * @package TBela\CSS\Value
 */
class CssAttribute extends Value {

    protected static function validate($data): bool {

        return isset($data->arguments) && (is_array($data->arguments) || $data->arguments instanceof Set);
    }

    public function render(array $options = []): string {

        return '['. $this->data->arguments->render($options).']';
    }

    public static function doRender(object $data, array $options = []) {

        return '['. Value::renderTokens($data->arguments, $options).']';
    }
}
