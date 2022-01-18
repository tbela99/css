#!/usr/bin/php
<?php

require 'autoload.php';

use TBela\CSS\Compiler;
use TBela\CSS\Parser;
use \TBela\CSS\Value;

$parser = new Parser();

// font: italic small-caps 16px/2 "Fira Sans", serif'

$parser->setContent('
h1 {
 color: #000
}
h1 {
 color: #f0f8ff
}
@media print, screen and (max-width: 10000px) {
 p {
   color: #435678;
   font: italic 14px "Fira Sans", serif;
   line-height: 2;
   font-weight: bold;
   font-size: 16px;
  font-variant: small-caps;
   padding-top: 5px /* 5px */;
   padding-right: 10px /* 10px */;
   padding-bottom: 10px /* 10px */;
   padding-left: 10px /* 10px */;
   margin-left: 10px /* 10px */;
   margin-right: 10px /* 10px */;
   margin-top: 10px /* 5px */;
   margin-bottom: 10px /* 10px */;
 }
}');

//echo $parser->parse(); // ->computeShortHand();
echo (new \TBela\CSS\Renderer([
        'compress' => true,
        'compute_shorthand' => true,
    //    'allow_duplicate_declarations' => true
]))->render($parser->parse() /* ->computeShortHand() */);