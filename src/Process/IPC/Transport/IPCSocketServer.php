<?php

namespace TBela\CSS\Process\IPC\Transport;

use TBela\CSS\Process\IPC\IPC;

class IPCSocketServer extends IPC
{

	const BUFFER_SIZE = 32768;
	protected \Socket|false|null $socket;
	protected string $path;
	protected string $data;

	public function __construct($path = null)
	{

		if (!($this->socket = socket_create(AF_UNIX, SOCK_DGRAM, 0))) {

			throw new \RuntimeException("Can't create socket", 500);
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

			throw new \RuntimeException("socket can't bind to $this->path", 500);
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

		$this->data = '';

		$i = 0;
		$j = strlen($data);

		global $log;

		if (isset($log)) {

			file_put_contents($log, "waiting for incoming connection ...\n", FILE_APPEND);
		}

		$socket = socket_accept($this->socket);

		if (isset($log)) {

			file_put_contents($log, "incoming connection accepted ...\n", FILE_APPEND);
		}

		while ($i < $j) {

			$written = socket_write($socket, substr($data, $i, static::BUFFER_SIZE), static::BUFFER_SIZE);

			if ($written === false) {

				break;
			}

			$i += $written;
		}

		socket_close($socket);
	}

	public function read(int $waitTimeout = 1): \Generator
	{

		fwrite(STDERR, sprintf("reading from %s\n\n", $this::class));

		$buffer = '';
		$seconds = floor($waitTimeout / 1000);

		$attempt = 5;

		while (!file_exists($this->path)) {

			clearstatcache(true, $this->path);
			fwrite(STDERR, sprintf("socket not ready $this->path\n"));

//			time_nanosleep(0, $waitTimeout);
			yield "waiting";
		}

//		if (!socket_connect($this->socket, $this->path)) {
//
//			throw new \RuntimeException(sprintf("socket can't connect to $this->path: %s", socket_strerror(socket_last_error())), 500);
//		}

		while (true) {

			$read = [$this->socket];
			$write = null;
			$except = null;

			$changed = socket_select($read, $write, $except, $seconds, $waitTimeout - 1000 * $seconds);

			if ($changed === false) {

				$status = socket_last_error($this->socket);

				if ($status) {

					throw new \RuntimeException(socket_strerror($status), 500);
				}
			}

			if (empty($read)) {

				yield "waiting";
				continue;
			}

			$data = socket_read($this->socket, static::BUFFER_SIZE);

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