<?php

namespace TBela\CSS\Process\Serialize;

use InvalidArgumentException;
use TBela\CSS\Process\Serialize\Format\JSONSerializer;
use TBela\CSS\Process\Serialize\Format\PHPSerializer;

abstract class Serializer
{

	abstract public function getName();

	/**
	 * @param mixed $data
	 * @return string
	 */
	abstract public function encode($data);

	/**
	 * @param string $data
	 * @return mixed
	 */
	abstract public function decode($data);

	public static function isSupported($format)
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

			if (class_exists($class) && $class::isSupported($format)) {

				return new $class;
			}

			throw new InvalidArgumentException(sprintf('unsupported format: %s', $format), 501);
		}

		if (is_callable('\\json_encode')) {

			return new JSONSerializer();
		}

		return new PHPSerializer();
	}
}