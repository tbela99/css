#!/usr/bin/php
<?php

use TBela\CSS\Ast\Traverser;
use TBela\CSS\Parser as CssParser;
use TBela\CSS\Renderer;

require 'autoload.php';

$parser = (new CssParser())->load(__DIR__ . '/ast/media.css');
$renderer = new Renderer(['remove_empty_nodes' => true]);

$ast = $parser->getAst();
$traverser = new Traverser();

$traverser->on('enter', function ($node) {
//
    // remove @media print { }
    if ($node->type == 'Declaration' && $node->name == 'line-height') {

        return Traverser::IGNORE_NODE;
    }
});

echo $renderer->renderAst($traverser->traverse($ast));