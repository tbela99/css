<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

/**
 * CSS function value
 * @package TBela\CSS\Value
 */
class CssParenthesisExpression extends Value {

    protected static function validate($data) {

        return isset($data->name) && $data->name === '' && isset($data->arguments);
    }

    public function render(array $options = []) {

        $filler = !empty($options['compress']) ? '' : ' ';

        return $filler.'('. $this->data->arguments->render($options).')'.$filler;
    }

    public function getHash() {

        return '('. $this->data->arguments->getHash().')';
    }
}
