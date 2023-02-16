<?php

namespace TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class ListStyleType extends Keyword
{

	protected static array $keywords = ['inside', 'outside', 'none'];
	public static array $defaults = ['none'];

	public static function matchToken($token, $previousToken = null, $previousValue = null, $nextToken = null, $nextValue = null, int $index = null, array $tokens = []): bool
	{

		return $token->type == 'css-string';
	}
}
