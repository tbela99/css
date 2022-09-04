<?php

namespace TBela\CSS\Process\IPC\Transport;

use Generator;
use RuntimeException;
use TBela\CSS\Process\IPC\IPC;

class IPCSocketPair extends IPC
{

	const BUFFER_SIZE = 32768;
	protected array $pair = [];
	protected string $data;

	public function __construct() {

		if (!socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $this->pair)) {

			throw new RuntimeException("Can't create sockets pair", 500);
		}
	}

	public static function isSupported(): bool
	{

		return function_exists('\\socket_create_pair');
	}

	public function write(string $data): void
	{

		$this->data = '';

		$i = 0;
		$j = strlen($data);

		while ($i < $j) {

			$written = socket_write($this->pair[1], substr($data, $i, static::BUFFER_SIZE));

			if ($written === false) {

				break;
			}

			$i += $written;
		}
	}

	public function read(int $waitTimeout = 1): Generator
	{

		$buffer = '';
		$seconds = floor($waitTimeout / 1000);

		while (true) {

			$read = [$this->pair[0]];
			$write = null;
			$except = null;

			$changed = @socket_select($read, $write, $except, $seconds, $waitTimeout - 1000 * $seconds);

			if ($changed === false) {

				$status = socket_last_error($this->pair[0]);

				if ($status) {

					throw new RuntimeException(socket_strerror($status), 500);
				}
			}

			$data = socket_read($this->pair[0], static::BUFFER_SIZE);

			if (is_string($data)) {

				$buffer .= $data;

				if (strlen($data) < static::BUFFER_SIZE) {

					break;
				}

				yield "reading";
			} else {

				yield "waiting";
			}
		}

		$this->data = $buffer;
		yield "done";
	}

	public function getData(): string
	{

		return $this->data;
	}

	public function release()
	{
		if (!empty($this->pair)) {

			socket_close($this->pair[1]);
			socket_close($this->pair[0]);

			$this->pair = [];
		}
	}

	public function __destruct() {

		$this->release();
	}
}