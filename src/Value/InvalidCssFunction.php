<?php

namespace TBela\CSS\Value;

use TBela\CSS\Interfaces\InvalidTokenInterface;
use \TBela\CSS\Value;

/**
 * CSS function value
 * @package TBela\CSS\Value
 */
class InvalidCssFunction extends Value implements InvalidTokenInterface {

    /**
     * @inheritDoc
     */
    protected static function validate($data): bool {

        return isset($data->name) && isset($data->arguments) && is_array($data->arguments);
    }

    /**
     * @inheritDoc
     */
    public function render(array $options = []): string {

        return $this->data->name.'('. $this->data->arguments->render($options);
    }

    /**
     * @inheritDoc
     */
    public function getValue() {

        return $this->data->arguments->{0}->value;
    }

    public static function doRecover(object $data):object {

        $result = clone $data;
        $result->type = substr($result->type, 8);

        return $result;
    }
}
