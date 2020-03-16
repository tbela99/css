<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

class Whitespace extends Value {

    protected static function validate($data) {

        return true;
    }

    public function getValue () {

        return ' ';
    }

    public function render(array $options = [])
    {
        return ' ';
    }
}
