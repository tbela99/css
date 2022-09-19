<?php

namespace TBela\CSS\Process\IPC;

use TBela\CSS\Process\IPC\Transport\IPCSocketServer;
use TBela\CSS\Process\IPC\Transport\IPCSocketPair;

abstract class IPC implements IPCInterface
{

	/**
	 * @param bool $pair use socket pair
	 * @return IPCInterface|null
	 */
	public static function getInstance(bool $pair = false): ?IPCInterface {

		// thread
		if ($pair && IPCSocketPair::isSupported()) {

			return new IPCSocketPair();
		}

		if (IPCSocketServer::isSupported()) {

			return new IPCSocketServer();
		}

		return null;
	}
}