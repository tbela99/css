<?php

namespace TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class BackgroundImage extends CssFunction
{

    use ValueTrait;

    public static array $keywords = ['none'];
    public static array $defaults = ['none'];

    /**
     * @inheritDoc
     */
    protected static function validate($data): bool {

        if (isset($data->value)) {

            return in_array($data->value, static::$keywords);
        }

        return isset($data->name) && isset($data->arguments) && (is_array($data->arguments) || $data->arguments instanceof Set);
    }

    public function render(array $options = []): string
    {
        return $this->data->value ?? parent::render($options);
    }

    public static function doRender(object $data, array $options = [])
    {
        return $data->value ?? parent::doRender($data, $options);
    }

    public static function matchToken($token, $previousToken = null, $previousValue = null, $nextToken = null, $nextValue = null, int $index = null, array $tokens = []): bool
    {

        return $token->type == static::type() || (isset($token->name) &&
                in_array($token->name, [
                    'url',
                    'linear-gradient',
                    'element',
                    'image',
                    'cross-fade',
                    'image-set'
                ]));
    }
}
