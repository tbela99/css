<?php

namespace TBela\CSS\Process\MultiProcessing;

use Opis\Closure\SerializableClosure;
use Symfony\Component\Process\PhpProcess;
use TBela\CSS\Event\EventTrait;
use TBela\CSS\Process\Exceptions\IllegalStateException;
use TBela\CSS\Process\IPC\IPC;
use TBela\CSS\Process\ProcessInterface;
use TBela\CSS\Process\Serialize\Serializer;

class Process implements ProcessInterface
{

	use EventTrait;
	protected PhpProcess $process;

	protected IPC $ipc;
	protected Serializer $serializer;
	protected mixed $data;
	protected ?float $endTime = null;
	protected bool $terminated = false;
	protected ?string $duration = null;
	protected ?int $exitCode = null;
	protected bool $started = false;
	protected bool $running = false;
	protected bool $stopped = false;

	public function __sleep() {

		return [];
	}

	public function __construct(\Closure $closure) {

		$autoload = '';

		foreach (get_included_files() as $file) {

			if (str_contains(str_replace(DIRECTORY_SEPARATOR, '/', $file), '/vendor/autoload.php')) {

				$autoload = $file;
				break;
			}
		}

		$serialized = new SerializableClosure($closure);

		$script = 'require "'.$autoload.'";';

		$vars = $serialized->getReflector()->getUseVariables();

		foreach ($vars as $key => $var) {

			$script .= "\n\$$key = ".var_export($var, true).";";
		}

		$code = $serialized->getReflector()->getCode();

//		if (IPCShmop::isSupported()) {
//
//			$this->ipc = new IPCShmop();
//			$this->serializer = Serializer::getInstance();
//
//			$key = $this->ipc->getKey();
//
//			$script .= sprintf("
//				\$ipc = new %s('%s');
//				/**
//				 * @var Serializer \$serializer
//				 */
//				\$serializer = new %s();
//				\$ipc->write(\$serializer->encode(call_user_func(%s)));
//			", IPCShmop::class, $key, $this->serializer::class, $code);
//
//			register_shutdown_function(function () {
//
//				$this->ipc->release();
//			});
//		}
//
//		else {

			$script .= "
			
				echo json_encode(call_user_func($code));
			";
//		}

		$script .= "exit;";

		$this->process = new PhpProcess("<?php $script ?>");

		if (isset($this->ipc)) {

			// disable stdin
			$this->process->setPty(true);
			// disable stdout
			$this->process->disableOutput();
		}
	}

	public static function isSupported(): bool
	{
		return function_exists('\\proc_open');
	}

	public function start(): void
	{
		$this->process->start();

		$this->started = true;
		$this->running = true;
	}

	public function stop(float $timeout = 10, int $signal = null): void
	{
		if ($this->stopped || $this->terminated) {

			return;
		}

		$this->exitCode = $this->process->stop($timeout, $signal);

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
		return $this->process->getStartTime();
	}

	public function getEndTime(): ?float
	{
		return $this->endTime;
	}

	public function getDuration(): ?string
	{
		return $this->duration;
	}

	public function getExitCode(): int
	{
		return $this->process->getExitCode();
	}

	/**
	 * @inheritDoc
	 * @throws IllegalStateException
	 */
	public function check(int $waitTimeout): \Generator
	{
		if (!$this->started || !$this->running) {

			throw new IllegalStateException('process must be started', 503);
		}

		while (!$this->process->isTerminated()) {

//			time_nanosleep(0, $waitTimeout * 10);
			yield "waiting";
		}

		$buffer = '';

		if (isset($this->ipc)) {

			foreach ($this->ipc->read($waitTimeout) as $status) {

				if ($status !== true && $status !== "done") {

					yield $status;
				}
			}

			$buffer = $this->ipc->getData();
			$this->data = $this->serializer->decode($buffer);
		}

		else {

			$buffer = $this->process->getOutput();
			$this->data = json_decode($buffer);
		}

		if (is_null($this->data) && !empty($buffer)) {

			throw new \RuntimeException(sprintf("invalid %s data?\n%s\n\n", $buffer), $this->serializer?->getName() ?? 'json', 500);
		}

		$this->endTime = microtime(true);
		$this->terminated = true;
		$this->running = false;
		$this->exitCode = $this->process->getExitCode();

		$diff = $this->endTime - $this->process->getStartTime();
		$this->duration = sprintf('%.2f%s', $diff < 1 ? $diff * 1000 : $diff, $diff < 1 ? 'ms' : 's');
		yield true;
	}

	public function getData()
	{
		return $this->data;
	}

	public function getStdErr(): string
	{
		return isset($this->ipc) ? '' : $this->process->getErrorOutput();
	}

//	public function __destruct() {
//
//		if (isset($this->ipc)) {
//
//			$this->ipc->release();
//		}
//	}

	public function isStopped(): bool
	{
		return $this->stopped;
	}

	public function setTimeout(float $timeout): void
	{
		$this->process->setTimeout($timeout);
	}

	public function getTimeout(): ?float
	{
		return $this->process->getTimeout();
	}
}