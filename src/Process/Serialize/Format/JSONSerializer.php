<?php

namespace TBela\CSS\Process\Serialize\Format;

use TBela\CSS\Process\Serialize\Serializer;

class JSONSerializer extends Serializer
{

	/**
	 * @inheritDoc
	 */
	public function encode(mixed $data): string
	{
		return json_encode($data);
	}

	/**
	 * @inheritDoc
	 */
	public function decode(string $data, int $options = 0): mixed
	{
		return json_decode($data, $options);
	}

	public function getName(): string
	{
		return 'json';
	}
}