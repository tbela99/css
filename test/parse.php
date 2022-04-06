#!/usr/bin/php
<?php

use TBela\CSS\Parser;
use TBela\CSS\Renderer;
use TBela\CSS\Value;

require 'autoload.php';

$comments = [];
var_dump(Value::format('border-collapse  /* collapse */', $comments), $comments);

//echo (new Parser())->load(__DIR__.'/files/test_2.css')->parse();

//echo new Parser('@media print /* comment 2 */ /* comment 3 */ {');