<?php

namespace TBela\CSS\Process\Serialize\Format;

use TBela\CSS\Process\Serialize\Serializer;

class PHPSerializer extends Serializer
{

	/**
	 * @inheritDoc
	 */
	public function encode(mixed $data): string
	{
		return serialize($data);
	}

	/**
	 * @inheritDoc
	 */
	public function decode(string $data): mixed
	{
		return unserialize($data);
	}

	public function getName(): string
	{
		return 'serialize';
	}
}