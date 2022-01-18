#!/usr/bin/php
<?php

require 'autoload.php';

use \TBela\CSS\Property\PropertyList;

$property = new PropertyList;


$propertyList = new PropertyList();

$propertyList->set('font', 'italic 1.2em "Fira Sans", serif');

echo $propertyList . "\n\n";

$propertyList->set('line-height', 2);
echo $propertyList . "\n\n";

$propertyList->set('font-weight', 'bold');
echo $propertyList . "\n\n";

$propertyList->set('font-size', '16px');
echo $propertyList . "\n\n";

$propertyList->set('font-variant', 'small-caps');
echo $propertyList . "\n\n";

$propertyList->set('font-weight', '400');
echo $propertyList . "\n\n";

$propertyList->set('font', '400 var(--default-font-size) \'Trebuchet MS\', sans-serif');
echo $propertyList . "\n\n";

$propertyList->set('font-size', '16px');
echo $propertyList . "\n\n";

$propertyList->set('font-weight', '400');
echo $propertyList . "\n\n";

$propertyList->set('font', '400 11px \'Trebuchet MS\', sans-serif');
echo $propertyList . "\n\n";

$propertyList->set('font-size', '16px');
echo $propertyList . "\n\n";

$propertyList->set('font-weight', 'bold');
echo $propertyList . "\n\n";