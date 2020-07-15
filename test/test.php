#!/usr/bin/php
<?php

require 'autoload.php';

use TBela\CSS\Compiler;
use TBela\CSS\Parser;
use \TBela\CSS\Value;

$parser = new Parser();
$compiler = new Compiler();

$data = [];

$file = './css/color.css';
$parser->setOptions(['allow_duplicate_declarations' => true, 'allow_duplicate_rules' => ['p']])->load($file);
$compiler->setOptions(['allow_duplicate_declarations' => true, 'convert_color' => 'hex']);

echo $compiler->setData($parser->parse())->compile();