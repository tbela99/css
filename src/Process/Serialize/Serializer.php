<?php

namespace TBela\CSS\Process\Serialize;

use TBela\CSS\Process\Serialize\Format\MSGPackSerializer;
use TBela\CSS\Process\Serialize\Format\PHPSerializer;

abstract class Serializer
{

	abstract public function getName(): string;

	/**
	 * @param mixed $data
	 * @return string
	 */
	abstract public function encode(mixed $data): string;

	/**
	 * @param string $data
	 * @return mixed
	 */
	abstract public function decode(string $data): mixed;

	public static function isSupported($format): bool
	{

		$format = strtolower($format);

		if ($format == 'serialize') {

			$format = 'php';
		}

		return class_exists(__NAMESPACE__.'\\Format\\'.strtoupper($format).'Serializer');
	}

	public static function getInstance(string $format = null) {

		if (!is_null($format)) {

			if (strtolower($format) == 'serialize') {

				$format = 'php';
			}

			$class = __NAMESPACE__.'\\Format\\'.strtoupper($format).'Serializer';

			if (class_exists($class)) {

				return new $class;
			}

			throw new \InvalidArgumentException(sprintf('unsupported format: %s', $format, 501));
		}

		if (function_exists('\msgpack_pack')) {

			return new MSGPackSerializer();
		}

		if (function_exists('\\json_encode')) {

			return new MSGPackSerializer();
		}

		return new PHPSerializer();
	}
}