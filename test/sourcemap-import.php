#!/usr/bin/php
<?php

use TBela\CSS\Compiler;
use TBela\CSS\Parser as CssParser;
use TBela\CSS\Property\PropertyList;
use TBela\CSS\Query\Parser as QueryParser;

require 'autoload.php';

//var_dump(CssParser\Helper::relativePath('/home/tbela/css/file.map', '/home/tbela/css/file.css'));
//var_dump(CssParser\Helper::relativePath('/home/tbela/map/file.map', '/home/tbela/css/file.css'));
//var_dump(CssParser\Helper::relativePath('/home/tbela/source/map/file.map', '/home/tbela/css/file.css'));
//var_dump(CssParser\Helper::relativePath('/home/tbela/sourcemap/file.map', '/home/tbela/resource/css/file.css'));
//var_dump(CssParser\Helper::relativePath('/home/tbela/resource/css/file.css', '/home/tbela/sourcemap/file.map'));
//var_dump(CssParser\Helper::relativePath('../resource/css/file.css', '../home/tbela/sourcemap/file.map'));
//var_dump(CssParser\Helper::relativePath('tbela/css/file.css', 'tbela/sourcemap/file.map'));
//var_dump(CssParser\Helper::relativePath('sourcemap/images/bg.png', 'sourcemap/generated/sourcemap.css'));
//var_dump(CssParser\Helper::relativePath('sourcemap/images/bg.png', 'sourcemap/generated/../sourcemap.css'));
//var_dump(CssParser\Helper::relativePath('http://google.com/sourcemap/images/bg.png', 'http://google.ca/sourcemap/generated/../sourcemap.css'));
//var_dump(CssParser\Helper::relativePath('http://google.com/sourcemap/images/bg.png', 'http://google.com/sourcemap/generated/../sourcemap.css'));
//var_dump(CssParser\Helper::relativePath('http://google.com/sourcemap/images/bg.png', 'generated/../sourcemap.css'));
//var_dump(CssParser\Helper::relativePath('/sourcemap/images/bg.png', 'http://google.com/sourcemap/generated/../sourcemap.css'));
//die;

$parser = (new CssParser('', [

    'flatten_import' => true
]))->load(__DIR__ . '/sourcemap/sourcemap.import.css');

$element = $parser->parse();

$renderer = new \TBela\CSS\Renderer([
    'sourcemap' => true
]);

$renderer->
    save($element, __DIR__.'/sourcemap/generated/sourcemap.import.flatten.css');
$renderer->setOptions([
    'compress' => true
])->save($element, __DIR__.'/sourcemap/generated/sourcemap.import.flatten.min.css');
