<?php

namespace TBela\CSS\Value;

use TBela\CSS\Value;

/**
 * Properties that only accept keywords
 * @package TBela\CSS\Value
 */
class Keyword extends Value
{
	use ParsableTrait;

	protected static string $propertyType = 'css-string';

	public static function doRender(object $data, array $options = [])
	{

		return $data->value;
	}
}
