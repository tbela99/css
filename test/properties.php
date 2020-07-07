#!/usr/bin/php
<?php

require 'autoload.php';

use \TBela\CSS\Property\PropertyList;


$property = new PropertyList;

$property->set('margin-top', '5px \9');
$property->set('margin-left', '5px \9');
$property->set('margin-bottom', '5px \9');
$property->set('margin-right', '5px \9');

echo $property."\n\n";

$property->set('margin-top', '5px');
$property->set('margin-left', '5px');
$property->set('margin-bottom', '5px');
$property->set('margin-right', '5px');

echo $property."\n\n";