<?php

//declare(ticks=1);

namespace TBela\CSS\Process\Thread\PCNTL;

use TBela\CSS\Event\EventTrait;
use TBela\CSS\Process\Exceptions\TimeoutException;
use TBela\CSS\Process\IPC\IPC;
use TBela\CSS\Process\ProcessInterface;
use TBela\CSS\Process\Serialize\Serializer;
use TBela\CSS\Process\Exceptions\IllegalStateException;

class Thread implements ProcessInterface
{

	use EventTrait;

	// 32k
	const BUFFER_SIZE = 32768;

	protected \Closure $task;

	protected array $pair = [];

	protected bool $started = false;
	protected bool $running = false;
	protected bool $terminated = false;
	protected mixed $data = null;

	protected bool $stopped = false;
	protected int $pid;

	/**
	 * @var float|null time in nanoseconds
	 */
	protected ?float $startTime = null;

	/**
	 * @var float|null time in nanoseconds
	 */
	protected ?float $endTime = null;
	protected Serializer $serializer;
	protected IPC $ipc;

	protected ?int $exitCode = null;
	protected float $timeout = 0;

	public function __construct(\Closure $task)
	{
		$this->task = $task;
		$this->ipc = IPC::getInstance();
		$this->serializer = Serializer::getInstance();

		register_shutdown_function(function () {

			$this->ipc->release();
		});
	}

	public static function isSupported(): bool
	{

		return 'cli' == php_sapi_name() &&
			extension_loaded('pcntl') &&
			extension_loaded('sockets');
	}

	/**
	 * @throws IllegalStateException
	 */
	public function start(): void
	{

		if ($this->started) {

			throw new IllegalStateException('thread is already started', 503);
		}

		$this->running = true;
		$this->started = true;
		$this->terminated = false;
		$this->startTime = hrtime(true) / 1000;

		pcntl_signal(SIGCHLD, function () {

			fwrite(STDERR, "SIGCHLD received\n");
			$this->stopped = true;
			$this->check(1);
		});

		$this->pid = pcntl_fork();

		if ($this->pid == -1) {

			throw new \RuntimeException('Cannot fork process', 500);
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

			posix_kill($this->pid, SIGTERM);
			pcntl_waitpid($this->pid, $this->exitCode);
		}

		$this->stopped = true;
		$this->terminated = true;
		$this->running = false;
	}

	public function isStarted(): bool
	{

		return $this->started;
	}

	public function isRunning(): bool
	{

		return $this->running;
	}

	public function isTerminated(): bool
	{

		return $this->terminated;
	}

	public function getStartTime(): ?float
	{

		return $this->startTime;
	}

	public function getEndTime(): ?float
	{

		return $this->endTime;
	}

	public function getDuration(): ?string
	{

		if (is_null($this->endTime)) {

			return null;
		}

		$duration = $this->endTime - $this->startTime;
		return sprintf("%.2f%s", $duration < 1000 ? $duration : $duration / 1000, $duration < 1000 ? 'ms' : 's');
	}

	public function getExitCode(): int
	{

		return $this->exitCode;
	}

	/**
	 * @param int $waitTimeout timeout in nanoseconds
	 * @return \Generator
	 * @throws IllegalStateException
	 * @throws \RuntimeException
	 */

	public function check(int $waitTimeout): \Generator
	{

		if (!$this->started || !$this->running) {

			throw new IllegalStateException('thread must be started', 503);
		}

		if ($this->terminated) {

			return;
		}

//		if (!$this->stopped) {
//
//			time_nanosleep(0, 50);
//		}

		foreach ($this->ipc->read($waitTimeout) as $data) {

			if ($data !== true && $data !== "done") {

				if ($this->timeout > 0 && microtime(true) - $this->startTime >= $this->timeout) {

					$this->stop();

					throw new TimeoutException('the task has timeout', 500);
				}

				yield $data;
			}
		}

		$buffer = $this->ipc->getData();

		$this->data = $this->serializer->decode($buffer);

		$this->running = false;
		$this->terminated = true;
		$this->endTime = hrtime(true) / 1000;

		if (is_null($this->data) && !empty($buffer)) {

			throw new \RuntimeException(sprintf("invalid %s data?\n%s\n\n", $buffer), $this->serializer->getName(), 500);
		}

		pcntl_waitpid($this->pid, $this->exitCode);

		$this->emit('notify', $this);

		yield true;
	}

	public function getData()
	{

		return $this->data;
	}

	public function __destruct()
	{

		if (!empty($this->pid)) {

			$this->ipc->release();
		}
	}

	public function getStdErr(): string
	{
		return '';
	}

	public function isStopped(): bool
	{
		return $this->stopped;
	}

	public function setTimeout(float $timeout): void
	{
		$this->timeout = $timeout;
	}

	public function getTimeout(): ?float
	{
		return $this->timeout;
	}
}