<?php

namespace TBela\CSS\Value;

use \TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class FontStretch extends Value
{

    use UnitTrait;
    /**
     * @var array
     * @ignore
     */
    protected static array $keywords = [
        'normal' => '100%',
        'semi-condensed' => '87.5%',
        'condensed' => '75%',
        'extra-condensed' => '62.5%',
        'ultra-condensed' => '50%',
        'semi-expanded' => '112.5%',
        'expanded' => '125%',
        'extra-expanded' => '150%',
        'ultra-expanded' => '200%'
    ];

    protected static array $defaults = ['normal', '100%'];

    /**
     * @inheritDoc
     */
	public static function doRender(object $data, array $options = [])
	{


		if (!empty($options['compress'])) {

            $value = $data->value;

            if (isset(static::$keywords[$value])) {

                return static::$keywords[$value];
            }
        }

        return $data->value;
    }

    /**
     * @inheritDoc
     */
    public static function keywords () : array {

        return array_keys(static::$keywords);
    }
}