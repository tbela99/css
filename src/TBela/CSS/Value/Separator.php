<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

/**
 * Css separator token
 * @package TBela\CSS\Value
 */
class Separator extends Value
{

    /**
     * @inheritDoc
     */
    protected static function validate($data): bool
    {
        return isset($data->value) && is_string($data->value);
    }

    /**
     * @inheritDoc
     */
    public function render(array $options = []): string
    {

        return $this->data->value;
    }
}
