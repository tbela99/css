<?php

require __DIR__.'/../vendor/autoload.php';

spl_autoload_register(function  ($name) {

	$parts = explode('\\', $name);

	if ($parts[0] == 'TBela' && isset($parts[1]) && $parts[1] == 'CSS') {

        array_splice($parts, 0, 2);
		array_unshift($parts, 'src');
	}

	$path = __DIR__.'/../'.implode('/', $parts).'.php';

	if (is_file($path)) {

		require_once ($path);
	}
});

// force multithreading or multiprocessing
\TBela\CSS\Process\Pool::setDefaultEngine(getenv('PROCESS_ENGINE'));

fwrite(STDERR, sprintf("current engine: %s\n", \TBela\CSS\Process\Pool::getDefaultEngine()));
putenv('PROCESS_ENGINE');