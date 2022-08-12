<?php

//use OldParser\Parser;
//$css = file_get_contents(__DIR__ . '/../test/css/template.css');
//$css = file_get_contents(__DIR__ . '/none.css');
//$css = file_get_contents(__DIR__ . '/php-net.css');
//$css = file_get_contents(__DIR__ . '/input/bootstrap.3.css');
//$css = file_get_contents(__DIR__ . '/input/bootstrap.4.css');
//$css = file_get_contents(__DIR__ . '/../test/perf_files/perf.css');

$compress = ($argv[1] ?? null) == '-c';

$options = [
    'compress' => $compress,
    'remove_comments' => true,
    'compute_shorthand' => false,
    'remove_empty_nodes' => true,
    'allow_duplicate_rules' => true,
    'allow_duplicate_declarations' => true
];

$filename = $argv[$compress ? 2 : 1];
$css = file_get_contents($filename);