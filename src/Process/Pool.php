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
class Pool implements EventInterface {

    use EventTrait;

    /**
     * @var Process[]
     */
    protected array $queue = [];

    protected int $concurrency = 20;

    protected int $count = 0;

    protected int $sleepTime = 30;

    public function __construct() {

        $this->concurrency = ceil(Helper::getCPUCount() * 2.5);
    }

    public function add(Process $process): static
    {

        $this->queue[$this->count++] = $process;

        return $this->check();
    }

    public function setConcurrency(int $concurrency): static
    {

        $this->concurrency = $concurrency;
        return $this;
    }

    public function setSleepTime(int $sleepTime): static
    {

        $this->sleepTime = $sleepTime;
        return $this;
    }

    protected function check(): static
    {

        $running = 0;

        foreach ($this->queue as $key => $process) {

            if ($process->isTerminated()) {

                $this->emit('finish', $process, $key);
                unset($this->queue[$key]);
            }

            if ($process->isRunning()) {

                $running++;
            }

            else if ($running >= $this->concurrency) {

                break;
            }

            else if (!$process->isStarted()) {

                $process->start();
                $running++;
            }
        }

        return $this;
    }

    public function wait(): static
    {

        while ($this->queue) {

            $this->check();

            if ($this->queue) {

                usleep($this->sleepTime);
            }
        }

        $this->count = 0;

        return $this;
    }
}


