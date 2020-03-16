#!/usr/bin/php
<?php

require 'autoload.php';

$property = new \TBela\CSS\Property\PropertyList();

/*
$property->set('margin-top', '1px \9');
echo $property."\n\n";

$property->set('margin-right', '1px \9');
echo $property."\n\n";

$property->set('margin-bottom', '1px \9');
echo $property."\n\n";

$property->set('margin-left', '1px \9');
echo $property."\n\n";
*/

$property->set('margin', '0 0 15px 15px ');
echo $property."\n\n";

$property->set('margin-left', '15px ');
echo $property."\n\n";

$property->set('margin-top', '15px ');
echo $property."\n\n";

$property->set('margin-top', '0px ');
echo $property."\n\n";

$property->set('margin-left', '0px ');
echo $property."\n\n";

$property->set('margin-top', '15px ');
echo $property."\n\n";

$property->set('margin-left', '0 ');
echo $property."\n\n";