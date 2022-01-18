#!/usr/bin/php
<?php

use TBela\CSS\Compiler;
use TBela\CSS\Parser as CssParser;
use TBela\CSS\Property\PropertyList;
use TBela\CSS\Query\Parser as QueryParser;

require 'autoload.php';

$parser = (new CssParser())->load(__DIR__ . '/sourcemap/sourcemap.css')->
    append(__DIR__ . '/sourcemap/sourcemap.2.css')->
    append(__DIR__ . '/sourcemap/sourcemap.media.css');

//echo $parser;die;
$element = $parser->parse();
//
//echo $element;die;

$renderer = new \TBela\CSS\Renderer([
    'sourcemap' => true
]);

//echo $element;

$renderer->
    save($element, __DIR__.'/sourcemap/generated/sourcemap.generated.css');
$renderer->setOptions(['compress' => true])->save($element, __DIR__.'/sourcemap/generated/sourcemap.generated.min.css');
