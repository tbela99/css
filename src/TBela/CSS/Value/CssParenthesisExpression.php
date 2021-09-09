<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

/**
 * CSS function value
 * @package TBela\CSS\Value
 */
class CssParenthesisExpression extends Value {

    protected static function validate($data): bool {

        return isset($data->name) && $data->name === '' && isset($data->arguments);
    }

    public function render(array $options = []): string {

        return '('. $this->data->arguments->render($options).')';
    }

    public function getHash() {

        return '('. $this->data->arguments->getHash().')';
    }
}
