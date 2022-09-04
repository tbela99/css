<?php

namespace TBela\CSS\Process;

use Generator;
use RuntimeException;
use TBela\CSS\Process\Exceptions\IllegalStateException;
use TBela\CSS\Process\Exceptions\TimeoutException;
use TBela\CSS\Process\IPC\IPCInterface;
use TBela\CSS\Process\Serialize\Serializer;

abstract class AbstractProcess implements ProcessInterface
{

	/**
	 * @var float|null time in nanoseconds
	 */
	protected ?float $startTime = null;

	/**
	 * @var float|null time in nanoseconds
	 */
	protected ?float $endTime = null;
	protected ?int $pid = null;
	protected float $timeout = 0;
	protected Serializer $serializer;

	protected bool $started = false;
	protected bool $running = false;
	protected bool $terminated = false;
	protected mixed $data = null;

	protected bool $stopped = false;
	protected IPCInterface $ipc;

	protected ?int $exitCode = null;
	protected string $err = '';

	abstract public function cleanup(): void;

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

	public function getPid(): ?int
	{

		return $this->pid;
	}

	public function kill(int $pid): bool {

		if (!is_null($this->pid)) {

			if (strncasecmp(PHP_OS, "win", 3) == 0) {

				exec(sprintf('taskkill /F /T /PID %d 2>&1', $pid), $output, $exitCode);
			}

			else {

				exec(sprintf('kill -9 -%d 2>&1', $pid), $output, $exitCode);
			}

			if ($exitCode > 1 && $this->isRunning()) {

				return false;
			}

			return true;
		}

		return false;
	}

	public function setTimeout(float $timeout): void
	{
		$this->timeout = $timeout;
	}

	public function getTimeout(): ?float
	{
		return $this->timeout;
	}

	/**
	 * @return void
	 * @throws TimeoutException
	 */
	public function checkTimeout(): void
	{
		if ($this->timeout > 0 && microtime(true) - $this->startTime >= $this->timeout) {

			$this->stop();

			throw new TimeoutException('the task has timed out', 500);
		}
	}

	public function getStartTime(): ?float
	{
		return $this->startTime;
	}

	public function getEndTime(): ?float
	{
		return $this->endTime;
	}

	public function getExitCode(): ?int
	{
		return $this->exitCode;
	}

	public function getDuration(): ?string
	{

		if (is_null($this->endTime)) {

			return null;
		}

		$duration = $this->endTime - $this->startTime;

		return sprintf("%.2f%s", $duration < 1 ? $duration * 1000 : $duration, $duration < 1 ? 'ms' : 's');
	}

	public function getData()
	{
		return $this->data;
	}

	public function getStdErr(): string
	{
		return $this->err;
	}

	public function isStopped(): bool
	{
		return $this->stopped;
	}
	/**
	 * @param int $waitTimeout timeout in nanoseconds
	 * @return Generator
	 * @throws IllegalStateException
	 * @throws RuntimeException|TimeoutException
	 */

	public function check(int $waitTimeout): Generator
	{

		if (!$this->started || !$this->running) {

			throw new IllegalStateException('thread must be started', 503);
		}

		if ($this->terminated) {

			return;
		}

		foreach ($this->ipc->read($waitTimeout) as $data) {

			if ($data !== true && $data !== "done") {

				$this->checkTimeout();

				yield $data;
			}
		}

		$buffer = $this->ipc->getData();
		$this->data = $this->serializer->decode($buffer);

		$this->stop();

		if (is_null($this->data) && !empty($buffer)) {

			throw new RuntimeException(sprintf("invalid %s data?\n%s\n\n", $this->serializer->getName(), $buffer), 500);
		}

		$this->ipc->release();

		$this->cleanup();

		yield true;
	}
}