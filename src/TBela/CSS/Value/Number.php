<?php

namespace TBela\CSS\Value;

use TBela\CSS\Value;

/**
 * Css number value
 * @package TBela\CSS\Value
 */
class Number extends Value
{
    /**
     * @inheritDoc
     */
    protected function __construct($data)
    {
        parent::__construct($data);

        if (str_contains($this->data->value, 'e')) {

            $value = (float)$this->data->value;

            if ($value == intval($value)) {

                $value = (int)$value;
            }

            $this->data->value = (string)$value;
        }
    }

    /**
     * @inheritDoc
     */
    public static function match(object $data, string $type): bool
    {

        return ($data->value == '0' && $type == 'unit') || $data->type == $type;
    }

    /**
     * @inheritDoc
     */
    protected static function validate($data): bool
    {

        return isset($data->value) && is_numeric($data->value) && $data->value !== '';
    }

    /**
     * @param string $value
     * @return string
     * @ignore
     */
    public static function compress(string $value, array $options = []): string
    {

        $value = explode('.', (float)$value);

        if (isset($value[1]) && $value[1] == 0) {

            unset($value[1]);
        }

        if (isset($value[1])) {

            // convert 0.20 to .2
            $value[1] = rtrim($value[1], '0');

            if ($value[0] == 0) {

                $value[0] = rtrim($value[0], '0');
            }

        } else if (!isset($options['property']) || !in_array($options['property'],
                // @see https://developer.mozilla.org/en-US/docs/Web/CSS/integer
                [
                    'column-count',
                    'counter-increment',
                    'counter-reset',
                    'grid-column',
                    'grid-row',
                    'z-index'
                ]
            )) {

            // convert 1000 to 1e3
            $value[0] = preg_replace_callback('#(0{3,})$#', function ($matches) {

                return 'e' . strlen($matches[1]);
            }, $value[0]);
        }

        return implode('.', $value);
    }

    /**
     * @inheritDoc
     */
    public function render(array $options = []): string
    {
        return static::doRender($this->data, $options);
    }

    public static function doRender(object $data, array $options = [])
    {


        if (!empty($options['compress'])) {

            return static::compress($data->value, $options);
        }

        return $data->value;
    }
}
