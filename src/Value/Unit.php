<?php

namespace TBela\CSS\Value;

/**
 * Css unit value
 * @package TBela\CSS\Value
 * @property-read string $unit
 */
class Unit extends Number {

    /**
     * @inheritDoc
     */
    protected static function validate($data): bool {

        return isset($data->unit) || (isset($data->value) && $data->value == '0') || in_array(strtolower($data->value), static::$keywords);
    }

    /**
     * @inheritDoc
     */
    public static function match (object $data, $type): bool {

        $dataType = strtolower($data->type);
        return $dataType == static::type() || ($type == 'number' && $data->value == 0);
    }

	/**
	 * @inheritDoc
	 */
	public static function matchToken($token, $previousToken = null, $previousValue = null, $nextToken = null, $nextValue = null, int $index = null, array $tokens = []): bool
	{

		return $token->type == 'unit' || in_array($token->value, static::$keywords) || $token->type == static::type();
	}

    public static function doRender(object $data, array $options = []) {

        /**
         * @see https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Types#quantities
         */
        if ($data->value == 0 && !in_array(strtolower($data->unit), ['s', 'ms', 'hz', 'khz', 'dpi', 'dpcm', 'dppx', 'x'])) {

            return '0'.(isset($options['omit_unit']) && isset($data->unit) && $options['omit_unit'] == false ? $data->unit : '');
        }

        $unit = !empty($options['omit_unit']) && $options['omit_unit'] == $data->unit ? '' : $data->unit;

        if ($data->value == 0) {

            $unit = strtolower($data->unit);

            if ($unit == 'ms') {

                $unit = 's';
            }

            else if ($unit == 'khz') {

                $unit = 'hz';
            }
        }

        if ($unit == 'dppx') {

            $unit = 'x';
        }

        if (!empty($options['compress'])) {

            $value = $data->value;

            if ($data->unit == 'ms' && $value >= 100) {

                $unit = 's';
                $value /= 1000;
            }

            return static::compress($value).$unit;
        }

        return $data->value.$unit;
    }

	/**
	 * @inheritDoc
	 * @throws \Exception
	 */
	protected static function doParse(string $string, bool $capture_whitespace = true, $context = '', $contextName = '', $preserve_quotes = false)
	{

		$type = static::type();
		$tokens = static::getTokens($string, $capture_whitespace, $context, $contextName);

		foreach ($tokens as $token) {

			if (static::matchToken($token)) {

				$token->value = static::stripQuotes($token->value);
				$token->type = $type;
			}
		}

		return static::reduce($tokens);
	}
}
