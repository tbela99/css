<?php

//declare(ticks=1);

namespace TBela\CSS\Process\Thread\PCNTL;

use Closure;
use RuntimeException;
use TBela\CSS\Event\EventTrait;
use TBela\CSS\Process\AbstractProcess;
use TBela\CSS\Process\Exceptions\TimeoutException;
use TBela\CSS\Process\IPC\IPC;
use TBela\CSS\Process\Serialize\Serializer;
use TBela\CSS\Process\Exceptions\IllegalStateException;

class Thread extends AbstractProcess
{

	use EventTrait;

	protected Closure $task;

	protected array $pair = [];

	public function __construct(Closure $task)
	{
		$this->task = $task;
		$this->ipc = IPC::getInstance(true);
		$this->serializer = Serializer::getInstance();

		register_shutdown_function(function () {

			$this->ipc->release();
		});
	}

	public static function isSupported(): bool
	{

		return 'cli' == PHP_SAPI && extension_loaded('pcntl');
	}

	/**
	 * @throws IllegalStateException
	 * @throws TimeoutException
	 */
	public function start(): void
	{

		if ($this->started) {

			throw new IllegalStateException('thread is already started', 503);
		}

		$this->running = true;
		$this->started = true;
		$this->terminated = false;
		$this->startTime = microtime(true);

		pcntl_signal(SIGCHLD, function () {

			if ($this->running) {

//				fwrite(STDERR, "SIGCHLD received\n");
				$this->stopped = true;
//				$this->check(1);
			}
		});

		$this->pid = pcntl_fork();

		if ($this->pid == -1) {

			throw new RuntimeException('Cannot fork process', 500);
		}

		if ($this->pid === 0) {

			$this->ipc->write($this->serializer->encode(call_user_func($this->task)));
			exit;
		}
	}

	public function stop(float $timeout = 10, int $signal = null): void
	{
		if ($this->stopped || $this->terminated) {

			return;
		}

		if ($this->pid > 0) {

			$this->kill($this->pid);
			pcntl_waitpid($this->pid, $this->exitCode);
		}

		$this->stopped = true;
		$this->terminated = true;
		$this->running = false;
		$this->pid = null;
	}

	/**
	 * @return void
	 */
	public function cleanup(): void
	{
		$this->running = false;
		$this->terminated = true;
		$this->endTime = microtime(true);

		if (!$this->stopped) {

			pcntl_waitpid($this->pid, $this->exitCode);
		}
	}
}