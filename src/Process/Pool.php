<?php

namespace TBela\CSS\Process;

use Symfony\Component\Process\Process;
use TBela\CSS\Event\EventInterface;
use TBela\CSS\Event\EventTrait;

/**
 * A simple Pool manager around symphony Process component.
 *
 * Usage:
 * <code>
 *
 * $pool = new Pool();
 *
 * $pool->on('finish', function (Process $process, $position) {
 *      echo "process #$position completed!";
 * });
 *
 * $pool->add(new Process(...));
 *
 * $pool->add(new Process(...));
 *
 * $pool->add(new Process(...));
 *
 * $pool->wait();
 * </code>
 */
class Pool implements EventInterface
{

	use EventTrait;

	/**
	 * @var Process[]
	 */
//    protected array $queue = [];

	protected $concurrency = 20;

    protected $count = 0;

	protected $sleepTime = 33;

	protected $storage;

	protected $current = null;

	public function __construct()
	{

		$this->concurrency = max(20, ceil(Helper::getCPUCount() * 2.5));
		$this->storage = new \SplObjectStorage();
	}

	public function add(Process $process)
	{

		$this->current = $process;
		$this->storage[$process] = (object)['data' => (object)['index' => $this->count++, 'stdout' => '', 'stderr' => ''], 'next' => []];

		$this->check();
		return $this;
	}

	public function then(\Closure $callable)
	{

		$process = $this->current;

		$data = $this->storage[$process];
		$data->next[] = $callable;
		$this->storage[$process] = $data;

		return $this;
	}

	public function setConcurrency($concurrency)
	{

		$this->concurrency = $concurrency;
		return $this;
	}

	public function setSleepTime($sleepTime)
	{

		$this->sleepTime = $sleepTime;
		return $this;
	}

	protected function check()
	{

		$running = 0;

		/**
		 * @var Process $process
		 */
		foreach ($this->storage as $process) {

			$data = $this->storage[$process]->data;

			if ($process->isTerminated()) {

				$running = max(0, $running--);

				foreach ($this->storage[$process]->next as $callable) {

					call_user_func($callable, $process, $data->stdout, $data->stderr, $data->index);
				}

				$this->emit('finish', $process, $data->stdout, $data->stderr, $data->index);
				$this->storage->detach($process);
				continue;
			}

			if ($process->isRunning()) {

				$running++;
			} else if ($running >= $this->concurrency) {

				break;
			} else if (!$process->isStarted()) {

				$process->start(function ($type, $buffer) use ($data) {

					$data->{'std' . $type} .= $buffer;
				});
				$running++;
			}
		}

		return $this->storage->count() > 0;
	}

	public function wait()
	{

		while ($this->check()) {

			usleep($this->sleepTime);
		}

		$this->count = 0;
		$this->current = null;
		$this->storage = new \SplObjectStorage();

		return $this;
	}
}


