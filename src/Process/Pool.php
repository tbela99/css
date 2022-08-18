<?php

namespace TBela\CSS\Process;

use Closure;
use Opis\Closure\SerializableClosure;
use TBela\CSS\Event\EventTrait;
use TBela\CSS\Process\Exceptions\TimeoutException;
use TBela\CSS\Process\Exceptions\UnhandledException;
use TBela\CSS\Process\MultiProcessing\Process;
use TBela\CSS\Process\Thread\PCNTL\Thread;

//use TBela\CSS\Process\Thread\PCNTL\Exceptions;
//use TBela\CSS\Process\Thread\PCNTL\Thread;
//

/**
 * Simple thread pool manager using pcntl extension
 *
 * Usage:
 *
 * if (Pool::isSupported()) {
 *
 *        $pool = new Pool();
 *
 *    $pool->on('finish', function (mixed $data, int $index, ?string $stderr, int $exitCode, ProcessInterface $thread) {
 *
 *            echo "sprintf(thread $index completed in %dns\n", $thread->getDuration());
 *            var_dump($data);
 *    }
 *
 *    $names = [ 'joe', 'jane', 'romney', 'suzan', 'bruce', 'jim', 'tania'];
 *
 *        for ($i = 0; $i < 10; $i++) {
 *
 *        $pool->add(function () use($names) {
 *
 *                // do something
 *                $index = random_int(1, count($names) - 1);
 *
 *                sleep($index);
 *                return sprintf("%s is having good sleep", $names[$index]);
 *            });
 *        }
 *
 *        $pool->wait();
 *    }
 */
class Pool implements PoolInterface
{
	use EventTrait;

	protected ?ProcessInterface $current = null;
	protected ?int $startTime = null;

	protected int $count = 0;
	protected int $concurrency = 25;

	/**
	 * @var int time in nanoseconds
	 */
	protected int $sleepTime = 90;
	protected \SplObjectStorage $storage;

	protected int $timeout = 15;

	/**
	 * @var ProcessInterface[]
	 */

	public function __construct()
	{

		if (!static::isSupported()) {

			throw new \RuntimeException('"pcntl" extension is required', 500);
		}

		$this->storage = new \SplObjectStorage();
		$this->concurrency = Helper::getCPUCount() * 2;
	}

	public static function isSupported(): bool
	{

		return Thread::isSupported() || Process::isSupported();
	}

	public function createProcess(Closure $closure): ProcessInterface
	{

		if (Thread::isSupported()) {

			return new Thread($closure);
		}

		if (Process::isSupported()) {

			return new Process($closure);
		}

		throw new \RuntimeException('cannot create process');
	}

	/**
	 */
	public function add(Closure $closure): static
	{

		$this->current = $this->createProcess($closure)->on('notify', function (ProcessInterface $thread) {

			$this->collect($thread);
		});

		$this->current->setTimeout($this->timeout);

		$this->storage[$this->current] = (object)['data' => (object)['index' => $this->count++, 'stdout' => '', 'stderr' => '', 'counter' => 0], 'next' => [], 'error' => []];

		$this->check(false);
		return $this;
	}

	/**
	 * @throws \ReflectionException
	 * @throws UnhandledException
	 */
	protected function check($collect = true): bool
	{

		$running = 0;

		/**
		 * @var ProcessInterface $thread
		 */
		foreach ($this->storage as $thread) {

			if ($running >= $this->concurrency) {

				break;
			}

			$data = $this->storage[$thread]->data;

			if ($collect && $thread->isTerminated()) {

				$this->collect($thread);
			} else if ($collect && $thread->isRunning()) {

				try {

					foreach ($thread->check(1) as $status) {

						if ($status === "waiting") {

							break;
						}

						if ($status === true) {

							$this->collect($thread);
							$running = max(0, $running - 1);
						}
					}
				}

				catch (\Throwable $e) {

					if ($e instanceof TimeoutException) {

						$this->storage->detach($thread);
					}

					$this->handleException($e, $data);
				}

			} else if (!$thread->isStarted()) {

				$thread->start();
				$this->emit('start', $data->index, $thread);
				$running++;
			}
		}

		return $this->storage->count() > 0;
	}

	protected function collect(ProcessInterface $thread)
	{

		if (!$this->storage->contains($thread)) {

			return;
		}

		$data = $this->storage[$thread];
		$result = $thread->getData();
		$stderr = $thread->getStdErr();
		$exitCode = $thread->getExitCode();
		$duration = $thread->getDuration();
		$index = $data->data->index;

		foreach ($data->next as $callable) {

			call_user_func($callable, $result, $index, $stderr, $exitCode, $duration, $thread);
		}

		$this->storage->detach($thread);
		$this->emit('finish', $result, $index, $stderr, $exitCode, $duration, $thread);
	}

	public function then(\Closure $callable): static
	{

		$process = $this->current;

		$data = $this->storage[$process];
		$data->next[] = $callable;
		$this->storage[$process] = $data;

		return $this;
	}

	public function catch(\Closure $callable): static
	{

		$process = $this->current;

		$data = $this->storage[$process];

		$parameters = (new SerializableClosure($callable))->getReflector()->getParameters();

		$this->assignErrorHandler($data, $callable, $parameters[0]?->getType());

		$this->storage[$process] = $data;

		return $this;
	}

	protected function assignErrorHandler(object $data, \Closure $callable, ?\ReflectionType $class)
	{

		if (is_null($class)) {

			$data->error['generic'] = $callable;
		} else if ($class instanceof \ReflectionNamedType) {

			$data->error[$class->getName()] = $callable;
		}

		else  if ($class instanceof \ReflectionUnionType || (class_exists('\\ReflectionIntersectionType') && $data instanceof \ReflectionIntersectionType)) {

			foreach ($class->getTypes() as $type) {

				$this->assignErrorHandler($data, $callable, $type);
			}
	}
	}

	public function setConcurrency(int $concurrency): static
	{

		$this->concurrency = $concurrency;
		return $this;
	}

	public function getConcurrency(): int
	{

		return $this->concurrency;
	}

	public function setSleepTime(int $sleepTime): static
	{

		$this->sleepTime = $sleepTime;
		return $this;
	}

	public function wait(): static
	{

//		static $count = 0;

		while ($this->check()) {

//			fwrite(STDERR, sprintf("pool waiting %d\n", $count++));
			time_nanosleep(0, $this->sleepTime);
		}

		$this->count = 0;
		$this->current = null;

		return $this;
	}

	public function cancel(): static
	{
		foreach ($this->storage as $thread) {

			$data = $this->storage[$thread];

			$this->storage->detach($thread);
			$thread->stop();

			$this->emit('cancel', $data->data->index, $thread);
		}

		return $this;
	}

	/**
	 * @param \Throwable $e
	 * @param $data
	 * @return void
	 * @throws UnhandledException
	 * @throws \ReflectionException
	 */
	protected function handleException(\Throwable $e, $data): void
	{
		$class = new \ReflectionClass($e::class);

		while ($class) {

			if (isset($data->error[$class->getName()])) {

				break;
			}

			$class = $class->getParentClass();
		}

		if ($class === false) {

			$class = null;
		}

		$handler = $data->error[$class?->getName()] ?? $data->error['generic'] ?? null;

		if (is_callable($handler)) {

			call_user_func($handler, $e);
		} else {

			throw new UnhandledException($e->getMessage(), $e->getCode(), $e);
		}
	}
}