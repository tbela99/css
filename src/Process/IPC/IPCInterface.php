<?php

namespace TBela\CSS\Process\IPC;

use Generator;

interface IPCInterface
{

	public static function isSupported(): bool;

	public function write(string $data): void;

	public function read(int $waitTimeout = 1): Generator;

	public function getData(): string;

	public function release();

}