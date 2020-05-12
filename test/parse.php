#!/usr/bin/php
<?php

require 'autoload.php';

use TBela\CSS\Property\PropertyList;

$property = new PropertyList();

//$property->set('outline', 'thick');
$property->set('outline-width', '0px');
$property->set('outline-style', 'none');
$property->set('outline-color', 'rebeccapurple');
//$property->set('outline', 'none');

echo $property;