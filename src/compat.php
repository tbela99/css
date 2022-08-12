<?php


if (!function_exists('mb_ord')) {

	function mb_ord($s, $encoding = null)
	{
		if (null === $encoding) {
			$s = mb_convert_encoding($s, 'UTF-8');
		} elseif ('UTF-8' !== $encoding) {
			$s = mb_convert_encoding($s, 'UTF-8', $encoding);
		}

		if (1 === \strlen($s)) {
			return \ord($s);
		}

		$code = ($s = unpack('C*', substr($s, 0, 4))) ? $s[1] : 0;
		if (0xF0 <= $code) {
			return (($code - 0xF0) << 18) + (($s[2] - 0x80) << 12) + (($s[3] - 0x80) << 6) + $s[4] - 0x80;
		}
		if (0xE0 <= $code) {
			return (($code - 0xE0) << 12) + (($s[2] - 0x80) << 6) + $s[3] - 0x80;
		}
		if (0xC0 <= $code) {
			return (($code - 0xC0) << 6) + $s[2] - 0x80;
		}

		return $code;
	}
}

if (!function_exists('stream_isatty')) {

	// from symphony/polyfill
	function stream_isatty($stream)
	{
		if (!\is_resource($stream)) {
			trigger_error('stream_isatty() expects parameter 1 to be resource, ' . \gettype($stream) . ' given', E_USER_WARNING);

			return false;
		}

		if ('\\' === \DIRECTORY_SEPARATOR) {
			$stat = @fstat($stream);
			// Check if formatted mode is S_IFCHR
			return $stat ? 0020000 === ($stat['mode'] & 0170000) : false;
		}

		return function_exists('posix_isatty') && @posix_isatty($stream);
	}
}

// source: Laravel Framework
// https://github.com/laravel/framework/blob/8.x/src/Illuminate/Support/Str.php
if (!function_exists('str_starts_with')) {
	function str_starts_with($haystack, $needle)
	{
		return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
	}
}
if (!function_exists('str_ends_with')) {
	function str_ends_with($haystack, $needle)
	{
		return $needle !== '' && substr($haystack, -strlen($needle)) === (string)$needle;
	}
}
if (!function_exists('str_contains')) {
	function str_contains($haystack, $needle)
	{
		return $needle !== '' && mb_strpos($haystack, $needle) !== false;
	}
}