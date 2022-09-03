<?php

namespace TBela\CSS\Process\IPC\Transport;

use TBela\CSS\Process\IPC\IPC;

class IPCStream extends IPC
{
	const BLOCK_SIZE = 32768;
	protected string $data;
	protected $stream;

	public function __construct($stream) {

		$this->stream = $stream;
		stream_set_blocking($stream, false);
	}

	public static function isSupported(): bool
	{
		return true;
	}

	public function write(string $data): void
	{

	}

	public function read(int $waitTimeout = 1): \Generator
	{

		$buffer = '';

		while (!feof($this->stream)) {

			$read = [$this->stream];
			$write = null;
			$except = null;

			$ready = stream_select($read, $write, $except, 0, $waitTimeout * 20000);

			if ($ready === false) {

				throw new \RuntimeException(sprintf("can't read from stream", 500));
			}

			if ($ready === 0) {

				yield 'waiting';
				continue;
			}

			yield 'reading';
			$data = fread($this->stream, static::BLOCK_SIZE);

			if ($data === false) {

				throw new \RuntimeException(sprintf("can't read from stream", 500));
			}

			$buffer .= $data;
		}

		$this->data = $buffer;
	}

	public function getData(): string
	{
		return $this->data;
	}

	public function release()
	{
		if ($this->stream) {

			fclose($this->stream);
			$this->stream = null;
		}
	}
}