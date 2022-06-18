#!/usr/bin/php
<?php

use TBela\CSS\Parser;
use \TBela\CSS\Renderer;

require 'autoload.php';

//$proertyList = new \TBela\CSS\Property\PropertyList();
//
//$proertyList->set('margin', '2px !important');
//$proertyList->set('margin-left', '3px !important');
//
//
//echo $proertyList;

$parser = new Parser('
  .btnflexanimate:hover{
      margin: 2px !important;
      margin-left: 3px !important;
  }');

echo $parser;

//var_dump($parser->getAst());

//echo $parser;