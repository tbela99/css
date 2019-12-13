<?php

spl_autoload_register(function  ($name) {

	$parts = explode('\\', $name);

	if ($parts[0] == 'TBela' && isset($parts[1]) && $parts[1] == 'CSS') {

		$parts[0] = 'src';
		unset($parts[1]);
	}

	$path = '../'.implode('/', $parts).'.php';

	if (is_file($path)) {

		require ($path);
	}
});