#!/usr/bin/php
<?php

require 'autoload.php';

use \TBela\CSS\Parser;
use \TBela\CSS\Compiler;

$parser = new Parser('', [
    'silent' => false,
    'minify' => false,
    'flatten_import' => true
]);

$parser->setOptions(['allow_duplicate_declarations' => true]);

$compiler = new Compiler(['compress' => true,'convert_color' => true]);
$compiler->load('./css/atrules.css');

file_put_contents('output/atrules.json', json_encode($compiler->getData()));