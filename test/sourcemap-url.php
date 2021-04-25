#!/usr/bin/php
<?php

use TBela\CSS\Compiler;
use TBela\CSS\Parser as CssParser;
use TBela\CSS\Property\PropertyList;
use TBela\CSS\Query\Parser as QueryParser;
use TBela\CSS\Renderer;

require 'autoload.php';

$parser = (new CssParser('', [
        'flatten_import' => true
]))->load('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/brands.min.css');

$element = $parser->parse();
//

$renderer = new Renderer([
    'sourcemap' => true
]);

$renderer->
    save($element, __DIR__.'/sourcemap/generated/sourcemap.generated-url.css');
$renderer->setOptions(['compress' => true])->save($element, __DIR__.'/sourcemap/generated/sourcemap.generated-url.min.css');
