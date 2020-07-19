#!/usr/bin/php
<?php

require 'autoload.php';

use TBela\CSS\Compiler;
use TBela\CSS\Parser;
use \TBela\CSS\Value;

$parser = new Parser();

// font: italic small-caps 16px/2 "Fira Sans", serif'

$parser->setContent('@media print, screen and (max-width: 12450px) {
 p {
   color: #f0f0f0;
   background-color: #030303
 }
}');

echo $parser->parse();