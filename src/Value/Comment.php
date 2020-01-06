<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

class Comment extends Value {

    protected static function validate($data) {

        return true;
    }

    public function render($compressed = false, array $options = [])
    {
        if ($compressed || !empty($options['remove_comments'])) {

            return '';
        }

        return $this->data->value;
    }
}
