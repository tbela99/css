<?php

namespace TBela\CSS\Process\IPC;

abstract class IPC implements IPCInterface
{

	public static function getInstance(): IPCInterface {

		if (IPCSocket::isSupported()) {

			return new IPCSocket();
		}

		throw new \RuntimeException("No IPC driver available");
	}
}