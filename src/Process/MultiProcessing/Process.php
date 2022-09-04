<?php

namespace TBela\CSS\Process\MultiProcessing;

use Closure;
use Opis\Closure\SerializableClosure;
use RuntimeException;
use TBela\CSS\Event\EventTrait;
use TBela\CSS\Process\AbstractProcess;
use TBela\CSS\Process\Exceptions\IllegalStateException;
use TBela\CSS\Process\IPC\IPCInterface;
use TBela\CSS\Process\IPC\Transport\IPCSocketClient;
use TBela\CSS\Process\IPC\Transport\IPCSocketServer;
use TBela\CSS\Process\Serialize\Serializer;

class Process extends AbstractProcess
{

	use EventTrait;

	protected $process;

	protected IPCInterface $ipc;
	protected Serializer $serializer;
	protected array $command;
	protected ?array $pipes;
	protected array $status;

	public function __construct(Closure $closure)
	{

		$autoload = '';

		foreach (get_included_files() as $file) {

			if (str_contains(str_replace(DIRECTORY_SEPARATOR, '/', $file), '/vendor/autoload.php')) {

				$autoload = $file;
				break;
			}
		}

		$serialized = new SerializableClosure($closure);

		$script = 'require "' . $autoload . '";';

		$vars = $serialized->getReflector()->getUseVariables();

		foreach ($vars as $key => $var) {

			$script .= "\n\$$key = " . var_export($var, true) . ";";
		}

		$code = $serialized->getReflector()->getCode();

		$this->ipc = new IPCSocketServer();
		$this->serializer = Serializer::getInstance();

		$key = $this->ipc->getKey();

		$script .= sprintf("

			try {
				\$data = call_user_func(%s);
				\$ipc = new %s(null, '%s');

				\$serializer = new %s();
				\$ipc->write(\$serializer->encode(\$data));
				}

			catch (Throwable \$e) {

			 fwrite(STDERR, \$e);
			}
			", $code, IPCSocketClient::class, $key, $this->serializer::class);

		$script .= "exit;";

		$file = tempnam(sys_get_temp_dir(), 'csr-');

		register_shutdown_function(function () use ($file) {

			@unlink($file);
			$this->cleanup();
		});

		file_put_contents($file, "<?php $script ?>");
		$this->command = [PHP_BINARY, '-f', escapeshellarg($file)];
	}

	public static function isSupported(): bool
	{
		return function_exists('\\proc_open') && extension_loaded('sockets');
	}

	public function start(): void
	{
		$this->startTime = microtime(true);

		$descriptorspec = [
			0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
			1 => array("pipe", "wb"),  // stdout is a pipe that the child will write to
			2 => array("pipe", "wb") // stderr is a file to write to
		];

		$this->process = proc_open(implode(' ', $this->command), $descriptorspec, $this->pipes);

		if ($this->process === false) {

			throw new RuntimeException(sprintf("failed to run command: '%'", implode(' ', $this->command)), 500);
		}

		$this->startTime = microtime(true);

		fclose($this->pipes[0]);
//		stream_set_blocking($this->pipes[1], false);
//		stream_set_blocking($this->pipes[2], false);

		$this->status = proc_get_status($this->process);

		$this->pid = $this->status['pid'];

		$this->started = true;
		$this->running = true;
	}

	/**
	 * @throws IllegalStateException
	 */
	public function stop(float $timeout = 10, int $signal = null): void
	{
		if (!$this->started) {

			throw new IllegalStateException('process must be started', 500);
		}

		if ($this->stopped || $this->terminated) {

			return;
		}

		$this->status = proc_get_status($this->process);

		if ($this->status['running']) {

			if (!$this->kill($this->pid)) {

				proc_terminate($this->process, 9);

				$this->status = proc_get_status($this->process);

				if ($this->status['running']) {

					throw new RuntimeException(sprintf("cannot kill process #%s", $this->getPid()));
				}
			}
		}

		$this->cleanup();
	}

	/**
	 * @return void
	 */
	public function cleanup(): void
	{
//		$this->status = proc_get_status($this->process);

		if ($this->process) {

			$this->running = false;
			$this->terminated = true;
			$this->endTime = microtime(true);

			$this->exitCode = max(0, $this->status['exitcode']);

			$this->err = stream_get_contents($this->pipes[2]);

			fclose($this->pipes[1]);
			fclose($this->pipes[2]);

			proc_close($this->process);
			$this->process = null;
			$this->pid = null;

			$this->pipes = [];
		}
	}
}