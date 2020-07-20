<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

/**
 * Css whitespace value
 * @package TBela\CSS\Value
 */
class Whitespace extends Value {

    /**
     * @inheritDoc
     */
    protected static function validate($data): bool {

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getValue () {

        return ' ';
    }

    /**
     * @inheritDoc
     */
    public function render(array $options = []): string
    {
        return ' ';
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        return ' ';
    }
}
