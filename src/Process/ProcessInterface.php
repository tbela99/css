<?php

namespace TBela\CSS\Process;

use Generator;
use RuntimeException;
use TBela\CSS\Event\EventInterface;
use TBela\CSS\Process\Exceptions\IllegalStateException;

interface ProcessInterface extends EventInterface
{

	public function start();

	public function stop($timeout = 10, $signal = null);

	public static function isSupported();

	public function isStarted();

	public function isStopped();

	public function isRunning();

	public function isTerminated();

	public function getStartTime();

	public function getEndTime();

	public function getDuration();

	public function getPid();

	public function kill($pid);

	public function getExitCode();

	public function setTimeout($timeout);

	public function getTimeout();

	/**
	 * @param int $waitTimeout timeout in nanoseconds
	 * @return Generator
	 * @throws IllegalStateException
	 * @throws RuntimeException
	 */

	public function check($waitTimeout);

	public function getData();

	public function getStdErr();
}