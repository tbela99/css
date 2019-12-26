#!/usr/bin/php
<?php

require 'autoload.php';
use TBela\CSS\Parser;
use TBela\CSS\Compiler;


$parser = new Parser('css/manipulate.css');

// test builder
$compiler = new Compiler();

$compiler->setData($parser->parse());

echo $compiler->compile()."\n\n\n";
echo $compiler->setOptions(['compress' => true, 'rgba_hex' => true])->compile()."\n\n\n";