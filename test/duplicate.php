#!/usr/bin/php
<?php

require 'autoload.php';

use TBela\CSS\Compiler;
use TBela\CSS\Parser;

$parser = new Parser();
$compiler = new Compiler();

$parser->setOptions(['allow_duplicate_declarations' => true]);
$compiler->setOptions(['allow_duplicate_declarations' => true]);
//

$parser->load('css/color.css');

$css = $compiler->setData($parser->parse())->compile();

file_put_contents('output/color.duplicate.css', $css);
echo $css;