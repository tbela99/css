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

    /**
     * @inheritDoc
     */
    protected static function validate($data): bool {

        if (isset($data->value)) {

            return in_array($data->value, static::$keywords);
        }

        return isset($data->name) && isset($data->arguments) && $data->arguments instanceof Set;
    }

    public function render(array $options = []): string
    {
        return $this->data->value ?? parent::render($options);
    }

    /**
     * @inheritDoc
     */
    public function getHash() {

        return $this->data->value ?? $this->data->name.'('. $this->data->arguments->getHash().')';
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
