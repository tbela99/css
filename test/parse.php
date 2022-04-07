#!/usr/bin/php
<?php

require 'autoload.php';

use TBela\CSS\Property\PropertyList;

$element = (new \TBela\CSS\Parser('
p {

}

p {

margin: 1px;
'))->parse();

$element->firstChild->setChildren([]);
$element->appendCss('

p {

margin: 1px;
');

$element->deduplicate();

echo $element;