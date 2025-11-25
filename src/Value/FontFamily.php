<?php

namespace TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class FontFamily extends ShortHand
{
    /**
     * @inheritDoc
     */
    public static function matchToken ($token, $previousToken = null, $previousValue = null, $nextToken = null, $nextValue = null, ?int $index = null, array $tokens = []): bool {

	protected static string $propertyType = 'css-string';
}
