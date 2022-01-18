#!/usr/bin/php
<?php

require 'autoload.php';
use \TBela\CSS\Element\Stylesheet;

$stylesheet = new Stylesheet();

$rule = $stylesheet->addRule('div');

$rule->addDeclaration('background-color', 'white');
$rule->addDeclaration('color', 'black');

$media = $stylesheet->addAtRule('media', 'print');
$media->append($rule);

$div = $stylesheet->addRule('div');

$div->addDeclaration('max-width', '100%');
$div->addDeclaration('border-width', '0px');


$media->append($div);

$stylesheet->insert($div, 0);

echo $stylesheet;