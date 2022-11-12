<?php

namespace TBela\CSS\Value;

trait ParsableTrait
{

	/**
	 * @inheritDoc
	 */
	public static function matchToken ($token, $previousToken = null, $previousValue = null, $nextToken = null, $nextValue = null, int $index = null, array $tokens = []): bool {

		return $token->type == static::$propertyType || (isset($token->value) && in_array($token->value, static::$keywords)) || $token->type == static::type();
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

				if ($token->type == static::$propertyType && isset($token->value)) {

					$token->value = static::stripQuotes($token->value);
				}

				$token->type = $type;
			}
		}

		return static::reduce($tokens);
	}
}