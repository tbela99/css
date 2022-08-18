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
	public function decode(string $data): mixed
	{
		return json_decode($data);
	}

	public function getName(): string
	{
		return 'json';
	}
}