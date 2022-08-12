<?php

namespace TBela\CSS\Parser;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use TBela\CSS\Process\Pool;

trait MultiprocessingTrait
{
	protected Pool $pool;
	protected array $output = [];
	/**
	 * @param $buffer
	 * @param array $cliArgs
	 * @return void
	 */
	protected function enQueue($buffer, array $cliArgs): void
	{

		if (!isset($this->pool)) {

			$this->pool = (new Pool())->
			on('finish', function (Process $process, $stdout, $stderr, $key) {

				if ($process->getExitCode() != 0) {

					throw new \RuntimeException($stderr, $process->getExitCode());
				}

				if (isset($this->format)) {

					$this->output[$key] = in_array($this->format, ['serialize', 'serialize-array']) ? unserialize($stdout) : json_decode($stdout);
				}

				else {

					$this->output[$key] = $stdout;
				}
			});
		}

		$phpPath = (new PhpExecutableFinder())->find();
		$cmd = array_merge([
			$phpPath,
			'-f',
			__DIR__ . '/../../cli/css-parser',
			'--'
		], $cliArgs);

		$this->output[] = null;

		$process = new Process($cmd);

		$process->setInput($buffer);
		$this->pool->add($process);
	}
}