<?php

namespace TBela\CSS\Process;

use Generator;
use RuntimeException;
use TBela\CSS\Event\EventInterface;
use TBela\CSS\Process\Exceptions\IllegalStateException;

interface ProcessInterface extends EventInterface
{

	public function start(): void;

	public function stop(float $timeout = 10, int $signal = null): void;

	public static function isSupported(): bool;

	public function isStarted(): bool;

	public function isStopped(): bool;

	public function isRunning(): bool;

	public function isTerminated(): bool;

	public function getStartTime(): ?float;

	public function getEndTime(): ?float;

	public function getDuration(): ?string;

	public function getPid(): ?int;

//	public function kill(int $pid): bool;

	public function getExitCode(): ?int;

	public function setTimeout(float $timeout): void;

	public function getTimeout(): ?float;

	/**
	 * @param int $waitTimeout timeout in nanoseconds
	 * @return Generator
	 * @throws IllegalStateException
	 * @throws RuntimeException
	 */

	public function check(int $waitTimeout): Generator;

	public function getData();

	public function getStdErr(): string;
}