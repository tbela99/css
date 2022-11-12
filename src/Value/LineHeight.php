<?php

namespace TBela\CSS\Value;

use \Exception;
use \TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class LineHeight extends Value
{
    use ValueTrait;

    protected static array $keywords = [
        'normal'
    ];

    protected static array $defaults = ['normal'];

	/**
	 * @inheritDoc
	 * @throws Exception
	 */
    public static function matchToken($token, $previousToken = null, $previousValue = null, $nextToken = null, $nextValue = null, int $index = null, array $tokens = []): bool
    {

        if (!is_null($previousToken) && $previousToken->type != 'separator' && (!isset($previousToken->value) || $previousToken->value != '/')) {

            return false;
        }

        if (!is_null($previousValue) && $previousValue->type != 'font-size') {

            throw new Exception('invalid "font" property: "font-size" expected before line-height '.$token->value, 400);
        }

        if ($token->type == 'unit' && !in_array($token->unit, ['turn', 'rad', 'grad', 'deg'])) {

            return true;
        }

        if ($token->type == 'number') {

            return true;
        }

        if ($token->type == 'css-string' && in_array(strtolower($token->value), static::$keywords)) {

            return true;
        }

        return $token->type == static::type();
    }

    public static function doRender(object $data, array $options = []): string
	{
        $value = $data->value;

        if ($value == '0') {

            return '0';
        }

        if (!empty($options['compress'])) {

            if(is_numeric($value)) {

                $value = Number::compress($value);
            }
        }

        if (isset($data->unit)) {

            return $value . $data->unit;
        }

        return $value;
    }
}
