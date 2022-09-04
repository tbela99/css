<?php

namespace TBela\CSS\Process\IPC\Transport;

use Generator;
use RuntimeException;
use TBela\CSS\Process\IPC\IPC;

class IPCSocketServer extends IPC
{

	const BUFFER_SIZE = 32768;
	protected \Socket|false|null $socket;
	protected string $path;
	protected string $data;

	public function __construct($path = null)
	{

		if (!($this->socket = socket_create(AF_UNIX, strcasecmp(substr(PHP_OS, 0, 3), 'win') === 0 ? SOCK_STREAM : SOCK_DGRAM, 0))) {

			throw new RuntimeException("Can't create socket", 500);
		}

		if (is_null($path)) {

			$this->path = tempnam(sys_get_temp_dir(), 'css-');
			unlink($this->path);
		} else {

			$this->path = $path;
		}

		register_shutdown_function(function () {

			@unlink($this->path);
		});

		if (!socket_bind($this->socket, $this->path)) {

			throw new RuntimeException("socket can't bind to $this->path", 500);
		}

		if (!socket_set_nonblock($this->socket)) {

			throw new RuntimeException("socket can't set non blocking socket", 500);
		}
	}

	public function getKey()
	{

		return $this->path;
	}

	public static function isSupported(): bool
	{

		return function_exists('\\socket_create');
	}

	public function write(string $data): void
	{
	}

	public function read(int $waitTimeout = 1): Generator
	{

		$buffer = '';

		while (true) {

			$read = [$this->socket];
			$write = null;
			$except = null;

			$changed = socket_select($read, $write, $except, 0, $waitTimeout * 20000);

			if ($changed === false) {

				throw new RuntimeException(socket_strerror(socket_last_error($this->socket)), 500);
			}

			if (empty($read)) {

				yield "waiting";
				continue;
			}

			$bytes_received = socket_recvfrom($this->socket, $data, static::BUFFER_SIZE, 0, $from);

			if ($bytes_received === false) {

				throw new RuntimeException(sprintf("Can't read from socket: %s", socket_strerror(socket_last_error($this->socket))), 500);
			}

			$buffer .= $data;

			if (strlen($data) < static::BUFFER_SIZE) {

				break;
			}

			yield "reading";
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
		if (!empty($this->socket)) {

			socket_close($this->socket);
			$this->socket = null;
		}
	}

	public function __destruct()
	{

		$this->release();
	}
}