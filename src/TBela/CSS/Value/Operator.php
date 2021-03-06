<?php

namespace TBela\CSS\Value;

use TBela\CSS\Value;

/**
 * Css number value
 * @package TBela\CSS\Value
 */
class Operator extends Value
{

    /**
     * @inheritDoc
     */
    public function match (string $type): bool {

        return $this->data->type == $type;
    }

    /**
     * @inheritDoc
     */
    protected static function validate($data): bool
    {

        return isset($data->value) && $data->value !== '';
    }

    /**
     * @inheritDoc
     */
    public function render(array $options = []): string
    {

        return $this->data->value;
    }
}
