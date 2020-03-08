#!/usr/bin/php
<?php

require 'autoload.php';

use TBela\CSS\Property\PropertyList;

// test builder

$data = [];

$property = new PropertyList();

$data[] = [$property, 'border-radius', '10% 17% 10% 17% / 50% 20% 50% 20%', 'border-radius: 10% 17%/50% 20%'];
/*
$data[] = [$property, 'border-top-left-radius', '1em 5em', 'border-radius: 1em 17% 10%/5em 20% 50%'];
$data[] = [$property, 'border-top-right-radius', '1em 5em', 'border-radius: 1em 1em 10% 17%/5em 5em 50% 20%'];
$data[] = [$property, 'border-bottom-left-radius', '1em 5em', 'border-radius: 1em 1em 10%/5em 5em 50%'];
$data[] = [$property, 'border-bottom-right-radius', '1em 5em', 'border-radius: 1em/5em'];
*/

foreach ($data as $args) {

    $property->set($args[1], $args[2]);

    echo $property."\n\n";
}