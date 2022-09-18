<?php

namespace TBela\CSS\Process\IPC;

use Generator;

interface IPCInterface
{

	public static function isSupported();

	public function write($data);

	public function read($waitTimeout = 1);

	public function getData();

	public function release();

}