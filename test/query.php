#!/usr/bin/php
<?php

use TBela\CSS\Compiler;
use TBela\CSS\Parser as CssParser;
use TBela\CSS\Property\PropertyList;
use TBela\CSS\Query\Parser as QueryParser;

require 'autoload.php';
//
//echo (new QueryParser())->parse(' [ data-catalyst ] ');
//die;

//$query = 'span|div,span|div, .div| span *, nav| *, .jdb-button-link,#jde-q2150115467813748jk .jdb-button-link, *[class*=jdb-container]';

//$query = file_get_contents(__DIR__.'/query.txt');
$element = (new CssParser())->load(__DIR__.'/perf_files/main.css')->parse();


//echo var_export(array_map('trim', $element->queryByClassNames($query)), true);
//echo $element;