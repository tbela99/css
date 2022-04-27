<?php

namespace TBela\CSS\Value;

use TBela\CSS\Interfaces\InvalidTokenInterface;
use \TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class InvalidCssString extends Value implements InvalidTokenInterface
{

    /**
     * @inheritDoc
     */
    protected static function validate($data): bool {

        return isset($data->q) && ($data->q == '"' || $data->q == "'") && isset($data->value) && !preg_match('#(?!\\\\)'.preg_quote($data->q, '#').'#', $data->value);
    }

    /**
     * @inheritDoc
     */
    public function getValue() {

        return $this->data->q.$this->data->value;
    }

    /**
     * @inheritDoc
     * @ignore
     */
    public function render(array $options = []): string
    {

        return $this->data->q.$this->data->value;
    }

    /**
     * @inheritDoc
     */
    public function recover($property = null): Value
    {

        return Value::getInstance((object) [
            'type' => 'css-string',
            'value' => $this->data->q.$this->value.$this->data->q
        ]);
    }

    public static function doRecover(object $data):object {

        $result = clone $data;
        $result->type = substr($result->type, 8);

        if (!empty($result->q) && preg_match('#^[\w_-]+$#', $result->value) && !is_numeric(\substr($result->value, 0, 1))) {

            unset($result->q);
        }

        return $result;
    }
}
