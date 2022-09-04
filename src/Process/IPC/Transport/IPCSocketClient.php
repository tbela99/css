<?php

namespace TBela\CSS\Process\IPC\Transport;

use Generator;
use RuntimeException;
use TBela\CSS\Process\IPC\IPC;

class IPCSocketClient extends IPC
{

	const BUFFER_SIZE = 32768;
	protected \Socket|false|null $socket;
	protected string $path;
	protected string $data;
	protected string $server;

	public function __construct(?string $path, string $server)
	{

		if (!($this->socket = socket_create(AF_UNIX, strcasecmp(substr(PHP_OS, 0, 3), 'win') === 0 ? SOCK_STREAM : SOCK_DGRAM, 0))) {

			throw new RuntimeException("Can't create socket", 500);
		}

		if (is_null($path)) {

			$this->path = tempnam(sys_get_temp_dir(), 'css-');
			unlink($this->path);
		}

		else {

			$this->path = $path;
		}

		register_shutdown_function(function () {

			@unlink($this->path);
		});

		if (!socket_bind($this->socket, $this->path)) {

			throw new RuntimeException("socket can't bind to $this->path", 500);
		}

		if (!socket_set_block($this->socket)) {

			throw new RuntimeException("socket can't set blocking socket", 500);
		}

		$this->server = $server;
	}

	public function getKey(): string
	{

		return $this->path;
	}

	public static function isSupported(): bool
	{

		return function_exists('\\socket_create');
	}

	public function write(string $data): void
	{

		$j = strlen($data) - 1;

		$i = 0;

		while($i < $j) {

			$bytes_sent = socket_sendto($this->socket, substr($data, $i, static::BUFFER_SIZE), static::BUFFER_SIZE, 0, $this->server);

			if ($bytes_sent === false) {

				throw new RuntimeException(sprintf("can't write to socket: %s", socket_strerror(socket_last_error($this->socket))), 500);
			}


			else {

				$i += $bytes_sent;
			}
		}
	}

	public function read(int $waitTimeout = 1): Generator
	{
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