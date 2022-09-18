<?php

namespace TBela\CSS\Process\Serialize\Format;

use TBela\CSS\Process\Serialize\Serializer;

class JSONSerializer extends Serializer
{

	/**
	 * @inheritDoc
	 */
	public function encode($data)
	{
		return json_encode($data);
	}

	/**
	 * @inheritDoc
	 */
	public function decode($data, $options = 0)
	{
		return json_decode($data, $options);
	}

	public function getName()
	{
		return 'json';
	}
}