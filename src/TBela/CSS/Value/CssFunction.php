<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

/**
 * CSS function value
 * @package TBela\CSS\Value
 */
class CSSFunction extends Value {

    /**
     * @inheritDoc
     */
    protected static function validate($data): bool {

        return isset($data->name) && isset($data->arguments) && $data->arguments instanceof Set;
    }

    /**
     * @inheritDoc
     */
    public function render(array $options = []): string {

        return $this->data->name.'('. $this->data->arguments->render($options).')';
    }

    /**
     * @inheritDoc
     */
    public function getValue() {

        return $this->data->arguments->{0}->value;
    }

    /**
     * @inheritDoc
     */
    public function getHash() {

        return $this->data->name.'('. $this->data->arguments->getHash().')';
    }
}
