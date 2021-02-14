#!/usr/bin/php
<?php

use TBela\CSS\Compiler;
use TBela\CSS\Parser as CssParser;
use TBela\CSS\Property\PropertyList;
use TBela\CSS\Query\Parser as QueryParser;

require 'autoload.php';

echo (new \TBela\CSS\Renderer([
        'preserve_license' => false,
        'remove_comments' => true,
        'compress' => true
    ]))->render(
        (new \TBela\CSS\Parser())->load(__DIR__.'/query/comments.css')->parse()
);