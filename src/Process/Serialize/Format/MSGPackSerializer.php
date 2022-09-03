<?php

namespace TBela\CSS\Process\Serialize\Format;

use TBela\CSS\Process\Serialize\Serializer;

class MSGPackSerializer extends Serializer
{

	/**
	 * @inheritDoc
	 */
	public function encode(mixed $data): string
	{
		return \msgpack_pack($data);
	}

	/**
	 * @inheritDoc
	 */
	public function decode(string $data): mixed
	{
		return \msgpack_unpack(rtrim($data));
	}

	public function getName(): string
	{
		return 'msgpack';
	}
}