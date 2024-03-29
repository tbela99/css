<?php

namespace TBela\CSS\Process;

use TBela\CSS\Event\EventInterface;

interface PoolInterface extends EventInterface
{

	public function cancel(): static;
	public function wait(): static;
}