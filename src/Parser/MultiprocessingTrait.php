<?php

namespace TBela\CSS\Parser;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use TBela\CSS\Parser;
use TBela\CSS\Process\Pool;

trait MultiprocessingTrait
{
	protected Pool $pool;
	protected array $output = [];

	public function slice($css, $size, $position = null): \Generator
	{

		$i = ($position->index ?? 0) - 1;
		$j = strlen($css) - 1;

		$buffer = '';

		while ($i++ < $j) {

			$string = Parser::substr($css, $i, $j, ['{']);

			if ($string === false) {

				$buffer = substr($css, $i);

				$pos = clone $position;
				$pos->index++;

				yield $position ? [$buffer, $pos] : $buffer;

				if ($position) {

					$this->update($position, $buffer);
					$position->index += strlen($buffer);
				}

				$buffer = '';
				break;
			}

			$string .= Parser::_close($css, '}', '{', $i + strlen($string), $j);
			$buffer .= $string;

			if (strlen($buffer) >= $size) {

				$k = 0;
				while (static::is_whitespace($buffer[$k])) {

					$k++;
				}

				if ($k > 0) {

					if ($position) {

						$this->update($position, substr($buffer, 0, $k));
						$position->index += $k;
					}

					$buffer = substr($buffer, $k);
				}

				if ($position) {

					$pos = clone $position;
					$pos->index++;

					yield [$buffer, $pos];

					$this->update($position, $buffer);
					$position->index += strlen($buffer);
				}

				else {

					yield $buffer;
				}

				$buffer = '';
			}

			$i += strlen($string) - 1;
		}

		if ($buffer) {

			$k = 0;
			$l = strlen($buffer);
			while ($k < $l && Parser::is_whitespace($buffer[$k])) {

				$k++;
			}

			if ($k > 0) {

				if ($position) {

					$this->update($position, substr($buffer, 0, $k));
					$position->index += $k;
				}

				$buffer = substr($buffer, $k);
			}
		}

		if (trim($buffer) !== '') {

			if ($position) {

				$pos = clone $position;
				$pos->index++;

				yield [$buffer, $pos];
				$this->update($position, $buffer);
				$position->index += strlen($buffer) - 1;
			}

			else {

				yield $buffer;
			}
		}

		if ($position) {

			$position->index = max(0, $position->index - 1);
			$position->column = max(1, $position->column - 1);
		}
	}

	protected function enQueue($src, $buffer, ?object $position, array $cliArgs)
	{

		if (!isset($this->pool)) {

			$this->pool = (new Pool())->
//			setConcurrency($this->options['children_process'])->
//                setSleepTime($this->sle)->
			on('finish', function (Process $process, $key) {

				$errorOutput = $process->getErrorOutput();

				if ($errorOutput) {

					fwrite(STDERR, $errorOutput);
				}

				if ($process->getExitCode() != 0) {

					throw new \RuntimeException($errorOutput, $process->getExitCode());
				}

				$payload = $process->getOutput();
//                $errorOutput = $process->getErrorOutput();

//				if ($errorOutput) {
//
//					fwrite(STDERR, $errorOutput);
//				}

				if (isset($this->format)) {

					$token = in_array($this->format, ['serialize', 'serialize-array']) ? unserialize($payload) : json_decode($payload);

//					var_dump( $payload);

					if (empty($token)) {

						// @todo throw new exception? log error? is is an error? maybe no
//                    fwrite(STDERR, sprintf("terminated: %s\ncmd: %s\ninput:\n%s\nexit code: %s\n\nerror: '%s'\n\n", $process->isTerminated() ? 'yes' : 'no', $process->getCommandLine(), $process->getInput(), $process->getExitCode(), $errorOutput));
					}

					else {

						$this->output[$key] = $token;
					}
				}

//				else {
//
//					$key = crc32($payload);
//
//					if (isset($this->output[$key])) {
//
//						unset($this->output[$key]);
//					}
//
//					$this->output[$key] = $payload;
//				}
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

//		fwrite(STDERR, implode(' ', $cmd)."\n\n");
//		fwrite(STDERR, implode(' ', $cmd)."\n\n$buffer\n\n");

		$process = new Process($cmd);

		$process->setInput($buffer);
		$this->pool->add($process);
	}

}