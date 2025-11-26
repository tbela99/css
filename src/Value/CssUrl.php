<?php

namespace TBela\CSS\Value;

use TBela\CSS\Value;

/**
 * CSS function value
 * @package TBela\CSS\Value
 */
class CssUrl extends CssFunction {

    protected static function validate($data): bool {

        return $data->name ?? null === 'url' && isset($data->arguments) && is_array($data->arguments);
    }

//    public function render(array $options = []): string {
//
//        return $this->data->name.'('. preg_replace('~^(["\'])([^\s\\1]+)\\1$~', '$2', $this->data->arguments->render($options)).')';
//    }

    /**
     * @inheritDoc
     */
    public static function doRender(object $data, array $options = [])
    {
        return $data->name.'('. preg_replace('~^(["\'])([^\s\\1]+)\\1$~', '$2', Value::renderTokens($data->arguments, $options)).')';
    }
}
