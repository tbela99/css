<?php

namespace TBela\CSS\Process;

use Closure;
use Opis\Closure\SerializableClosure;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use SplObjectStorage;
use TBela\CSS\Event\EventTrait;
use TBela\CSS\Process\Exceptions\TimeoutException;
use TBela\CSS\Process\Exceptions\UnhandledException;
use TBela\CSS\Process\MultiProcessing\Process;
use TBela\CSS\Process\Thread\PCNTL\Thread;
use Throwable;

/**
 * Simple thread pool manager using pcntl extension
 *
 * Usage:
 *
 * if (Pool::isSupported()) {
 *
 *        $pool = new Pool();
 *
 *    $pool->on('finish', function (mixed $data, $index, $stderr, $exitCode, ProcessInterface $process) {
 *
 *            echo "sprintf(thread $index completed in %dns\n", $process->getDuration());
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

	/**
	 * @var string|null default engine
	 */
	protected static $defaultEngine = null;
	protected $current = null;
	protected $startTime = null;

	protected $count = 0;
	protected $concurrency = 25;

	/**
	 * @var time in nanoseconds
	 */
	protected $sleepTime = 33000;
	protected $storage;

	protected $timeout = 60;
	protected $engine;

	public function __construct()
	{

		$this->storage = new SplObjectStorage();
		$this->concurrency = Helper::getCPUCount() * 2;
		$this->engine = static::getDefaultEngine();
	}

	public static function isSupported()
	{

		return false;
		return Thread::isSupported() || Process::isSupported();
	}

	public static function getAvailableEngines()
	{

		$result = [];

		if (Thread::isSupported()) {

			$result[] = 'thread';
		}

		if (Process::isSupported()) {

			$result[] = 'process';
		}

		return $result;
	}

	public function setEngine(string $engine)
	{

		if (in_array($engine, static::getAvailableEngines())) {

			$this->engine = $engine;
		}

		return $this;
	}

	public function getEngine()
	{

		return $this->engine;
	}

	public static function getDefaultEngine()
	{

		return isset(static::$defaultEngine) ? static::$defaultEngine : current(static::getAvailableEngines());
	}

	public static function setDefaultEngine($engine)
	{

		if ($engine == 'thread' && Thread::isSupported()) {

			static::$defaultEngine = 'thread';
		} else if ($engine == 'process' && Process::isSupported()) {

			static::$defaultEngine = 'process';
		} else if (is_null($engine)) {

			static::$defaultEngine = null;
		}
	}

	/**
	 * @param Closure $closure
	 * @return ProcessInterface
	 */
	public function createProcess(Closure $closure)
	{
		if ($this->engine == 'thread') {
			
			return new Thread($closure);
		}

		return new Process($closure);
	}

	/**
	 * @throws ReflectionException
	 * @throws UnhandledException
	 */
	public function add(Closure $closure)
	{

		$this->current = $this->createProcess($closure);

		$this->current->setTimeout($this->timeout);

		$this->storage[$this->current] = (object)['data' => (object)['index' => $this->count++, 'stdout' => '', 'stderr' => '', 'counter' => 0], 'next' => [], 'error' => []];

		$this->check(false);
		return $this;
	}

	/**
	 * @throws ReflectionException
	 * @throws UnhandledException
	 */
	protected function check($collect = true)
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
				} catch (Throwable $e) {

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

	public function then(Closure $callable)
	{

		$process = $this->current;

		$data = $this->storage[$process];
		$data->next[] = $callable;
		$this->storage[$process] = $data;

		return $this;
	}

	public function except(Closure $callable)
	{

		$process = $this->current;

		$data = $this->storage[$process];

		$parameters = (new SerializableClosure($callable))->getReflector()->getParameters();

		$this->assignErrorHandler($data, $callable, isset($parameters[0]) && is_callable([$parameters[0], 'getType']) ? $parameters[0]->getType() : null);

		$this->storage[$process] = $data;

		return $this;
	}

	protected function assignErrorHandler($data, Closure $callable, ReflectionType $class = null)
	{

		if (is_null($class)) {

			$data->error['generic'] = $callable;
		} else if ($class instanceof ReflectionNamedType) {

			$data->error[$class->getName()] = $callable;
		} else if ($class instanceof ReflectionUnionType || (class_exists('\\ReflectionIntersectionType') && $data instanceof \ReflectionIntersectionType)) {

			foreach ($class->getTypes() as $type) {

				$this->assignErrorHandler($data, $callable, $type);
			}
		}
	}

	public function setConcurrency($concurrency)
	{

		$this->concurrency = $concurrency;
		return $this;
	}

	public function getConcurrency()
	{

		return $this->concurrency;
	}

	public function setSleepTime($sleepTime)
	{

		$this->sleepTime = $sleepTime;
		return $this;
	}

	/**
	 * @throws ReflectionException
	 * @throws UnhandledException
	 */
	public function wait()
	{

		while ($this->check()) {

			time_nanosleep(0, $this->sleepTime);
		}

		$this->count = 0;
		$this->current = null;

		return $this;
	}

	public function cancel()
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
	 * @param Exception $e
	 * @param $data
	 * @return void
	 * @throws UnhandledException
	 * @throws ReflectionException
	 */
	protected function handleException(Exception $e, $data)
	{
		$class = new ReflectionClass(get_class($e));

		while ($class) {

			if (isset($data->error[$class->getName()])) {

				break;
			}

			$class = $class->getParentClass();
		}

		if ($class === false) {

			$class = null;
		}

		$property = is_callable([$class, 'getName']) ? $class->getName() : null;
		$handler = isset($data->error[$property]) ? $data->error[$property] : (isset($data->error['generic']) ? $data->error['generic'] : null);

		if (is_callable($handler)) {

			call_user_func($handler, $e);
		} else {

			throw new UnhandledException(sprintf("unhandled exception in task #%d", $data->index), $e->getCode(), $e);
		}
	}
}