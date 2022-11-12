<?php

namespace TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class FontFamily extends ShortHand
{
	use ParsableTrait;

	protected static string $propertyType = 'css-string';
}
