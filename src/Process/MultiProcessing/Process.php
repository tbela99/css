<?php

namespace TBela\CSS\Process\MultiProcessing;

use Generator;
use Opis\Closure\SerializableClosure;
use Symfony\Component\Process\PhpProcess;
use TBela\CSS\Event\EventTrait;
use TBela\CSS\Process\Exceptions\IllegalStateException;
use TBela\CSS\Process\Exceptions\TimeoutException;
use TBela\CSS\Process\IPC\IPCInterface;
use TBela\CSS\Process\IPC\Transport\IPCSocketServer;
use TBela\CSS\Process\ProcessInterface;
use TBela\CSS\Process\Serialize\Serializer;

class Process implements ProcessInterface
{

	use EventTrait;
	protected PhpProcess $process;

	protected IPCInterface $ipc;
	protected Serializer $serializer;
	protected mixed $data;
	protected ?float $endTime = null;
	protected bool $terminated = false;
	protected ?string $duration = null;
	protected ?int $exitCode = null;
	protected bool $started = false;
	protected bool $running = false;
	protected bool $stopped = false;
	protected float $timeout = 0;
	protected float $startTime;

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

		static $count = 0;

//		if (IPCSocketServer::isSupported()) {
//
//			$this->ipc = new IPCSocketServer();
//			$this->serializer = Serializer::getInstance();
//
//			$key = $this->ipc->getKey();
//
//			$script .= sprintf("
//
//			\$log = 'debug%s.log';
//				file_put_contents(\$log, \"started script \$log\\n\");
//
//			try {
//				\$data = call_user_func(%s);
//				file_put_contents(\$log, sprintf(\"writing data %%s\\n\", json_encode(\$data)), FILE_APPEND);
//				\$ipc = new %s('%s');
//
//				file_put_contents(\$log, \"waiting for connections on \".\$ipc->getKey().\"\\n\", FILE_APPEND);
//
//				/**
//				 * @var Serializer \$serializer
//				 */
//				\$serializer = new %s();
//				file_put_contents(\$log, \"writing data\\n\", FILE_APPEND);
//				\$ipc->write(\$serializer->encode(\$data));
//				}
//
//			catch (Throwable \$e) {
//
//				file_put_contents(\$log, \$e, FILE_APPEND);
//			}
//			", $count++, $code, $this->ipc::class, $key, $this->serializer::class);
//		}
//
//		else {

			$script .= "
			
				echo json_encode(call_user_func($code));
			";
//		}

		$script .= "exit;";

/*		file_put_contents('script'.($count - 1).'.php', "<?php $script ?>");*/

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
		$this->startTime = microtime(true);
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
		return $this->starTime;
	}

	public function getEndTime(): ?float
	{
		return $this->endTime;
	}

	public function getDuration(): ?string
	{
		return $this->duration;
	}

	public function getExitCode(): ?int
	{
		return $this->process->getExitCode();
	}

	/**
	 * @inheritDoc
	 * @throws IllegalStateException|TimeoutException
	 */
	public function check(int $waitTimeout): Generator
	{
		if (!$this->started || !$this->running) {

			throw new IllegalStateException('process must be started', 503);
		}

		fwrite(STDERR, sprintf("checking for readiness\n"));

		$buffer = '';

		if (isset($this->ipc)) {

			foreach ($this->ipc->read($waitTimeout) as $status) {

				if ($status !== true && $status !== "done") {

					$this->checkTimeout();
					yield $status;
				}
			}

			$buffer = $this->ipc->getData();
			$this->data = $this->serializer->decode($buffer);
		}

		else {

			while (!$this->process->isTerminated()) {

//			time_nanosleep(0, $waitTimeout * 10);

				$this->checkTimeout();
				yield "waiting";
			}

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
		$this->timeout = $timeout;
		$this->process->setTimeout($timeout);
	}

	public function getTimeout(): ?float
	{
		return $this->process->getTimeout();
	}

	/**
	 * @return void
	 * @throws TimeoutException
	 */
	public function checkTimeout(): void
	{
		fwrite(STDERR, sprintf("%.2f - %.2f\n\n", microtime(true) - $this->startTime, $this->timeout));

		if ($this->timeout > 0 && microtime(true) - $this->startTime >= $this->timeout) {

			$this->stop();

			throw new TimeoutException('the task has timed out', 500);
		}
	}
}