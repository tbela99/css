<?php

namespace TBela\CSS\Value;

use TBela\CSS\Interfaces\InvalidTokenInterface;
use \TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class InvalidComment extends Value implements InvalidTokenInterface
{

    /**
     * @inheritDoc
     * @ignore
     */
    public function render(array $options = [])
    {

        return '';
    }

    /**
     * invalid comments are discarded
     * @inheritDoc
     */
    public function recover($property = null)
    {

        return Value::getInstance((object) [
            'type' => 'css-string',
            'value' => ''
        ]);
    }

    public static function doRecover(object $data): object
    {
        return (object) ['type' => 'css-string', 'value' => '', 'q' => ''];
    }
}
