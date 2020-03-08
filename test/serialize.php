#!/usr/bin/php
<?php

require 'autoload.php';

use \TBela\CSS\Parser;
use \TBela\CSS\Compiler;

$parser = new Parser('', [
    'silent' => false,
    'minify' => true,
    'flatten_import' => true
]);

$parser->setOptions(['allow_duplicate_declarations' => true]);

$compiler = new Compiler(['compress' => true,'rgba_hex' => true]);
$compiler->load('./css/atrules.css');

file_put_contents('output/atrules.json', json_encode($compiler->getData()));