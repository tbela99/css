<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

/**
 * CSS function value
 * @package TBela\CSS\Value
 */
class CssAttribute extends Value {

    protected static function validate($data): bool {

        return isset($data->arguments) && $data->arguments instanceof Set;
    }

    public function render(array $options = []): string {

        return '['. $this->data->arguments->render($options).']';
    }
}
