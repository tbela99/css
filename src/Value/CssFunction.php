<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

class CSSFunction extends Value {

    protected static function validate($data) {

        return isset($data->name) && isset($data->arguments);
    }

    public function render($compress = false, array $options = []) {

        return $this->data->name.'('. $this->data->arguments->render($compress, $options).')';
    }
}