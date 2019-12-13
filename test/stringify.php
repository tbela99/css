#!/usr/bin/php
<?php

require 'autoload.php';


$compiler = new \TBela\CSS\Compiler([
    'charset' => false,
    'compress' => true,
    'rgba_hex' => false,
    'remove_comments' => false,
    'remove_empty_nodes' => true
]);

$compiler->setData(json_decode(file_get_contents('out.json')));

file_put_contents('out.css', $compiler->compile());