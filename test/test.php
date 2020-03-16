#!/usr/bin/php
<?php

require 'autoload.php';

use TBela\CSS\Property\PropertyList;

// test builder

$data = [];

/*
$property = new PropertyList();

$data[] = [$property, 'border-radius', '10% 17% 10% 17% / 50% 20% 50% 20%', 'border-radius: 10% 17%/50% 20%'];
$data[] = [$property, 'border-top-left-radius', '1em 5em', 'border-radius: 1em 17% 10%/5em 20% 50%'];
$data[] = [$property, 'border-top-right-radius', '1em 5em', 'border-radius: 1em 1em 10% 17%/5em 5em 50% 20%'];
$data[] = [$property, 'border-bottom-left-radius', '1em 5em', 'border-radius: 1em 1em 10%/5em 5em 50%'];
$data[] = [$property, 'border-bottom-right-radius', '1em 5em', 'border-radius: 1em/5em'];
*/

/*
$property2 = new PropertyList();

$data[] = [$property2, '-moz-border-radius', '10% 17% 10% 17% / 50% 20% 50% 20%', '-moz-border-radius: 10% 17%/50% 20%'];
$data[] = [$property2, '-moz-border-radius-topleft', '1em 5em', '-moz-border-radius: 1em 17% 10%/5em 20% 50%'];
$data[] = [$property2, '-moz-border-radius-topright', '1em 5em', '-moz-border-radius: 1em 1em 10% 17%/5em 5em 50% 20%'];
$data[] = [$property2, '-moz-border-radius-bottomright', '1em 5em', '-moz-border-radius: 1em 1em 10%/5em 5em 50%'];
$data[] = [$property2, '-moz-border-radius-bottomleft', '1em 5em', '-moz-border-radius: 1em/5em'];
*/

/*
$property3 = new PropertyList();

$data[] = [$property3, '-webkit-border-radius', '10% 17% 10% 17% / 50% 20% 50% 20%', '-webkit-border-radius: 10% 17%/50% 20%'];
$data[] = [$property3, '-webkit-border-top-left-radius', '1em 5em', '-webkit-border-radius: 1em 17% 10%/5em 20% 50%'];
$data[] = [$property3, '-webkit-border-top-right-radius', '1em 5em', '-webkit-border-radius: 1em 1em 10% 17%/5em 5em 50% 20%'];
$data[] = [$property3, '-webkit-border-bottom-left-radius', '1em 5em', '-webkit-border-radius: 1em 1em 10%/5em 5em 50%'];
$data[] = [$property3, '-webkit-border-bottom-right-radius', '1em 5em', '-webkit-border-radius: 1em/5em'];

$property4 = new PropertyList();

$data[] = [$property4, '-webkit-border-top-left-radius', '1em 5em', '-webkit-border-radius: 1em 5em'];
$data[] = [$property4, '-webkit-border-top-right-radius', '1em 5em', ''];
$data[] = [$property4, '-webkit-border-bottom-left-radius', '1em 5em', ''];
$data[] = [$property4, '-webkit-border-bottom-right-radius', '1em 5em', '-webkit-border-radius: 1em/5em'];

$property1 = new PropertyList();

$data[] = [$property1, 'margin', '0 0 15px 15px', 'margin: 0 0 15px 15px'];
$data[] = [$property1, 'margin-left', '15px', 'margin: 0 0 15px 15px'];
$data[] = [$property1, 'margin-top', '15px', 'margin: 15px 0 15px 15px'];
$data[] = [$property1, 'margin-top', '0px', 'margin: 0 0 15px 15px'];
$data[] = [$property1, 'margin-left', '0px', 'margin: 0 0 15px'];
$data[] = [$property1, 'margin-top', '15px', 'margin: 15px 0'];
$data[] = [$property1, 'margin-left', '0', 'margin: 15px 0'];
*/

$property5 = new PropertyList();

$property5->set('border-top-left-radius', '1em 5em');
echo '#0'."\n";
echo $property5."\n\n";

echo '#1'."\n";
$property5->set('border-top-right-radius', '1em 5em');
echo $property5."\n\n";


echo '#2'."\n";
$property5->set('border-bottom-left-radius', '1em 5em');
echo $property5."\n\n";

echo '#3'."\n";
$property5->set('border-bottom-right-radius', '1em 4em');
echo $property5."\n\n";

echo '#3'."\n";
$property5->set('border-bottom-right-radius', '1em 5em');
echo $property5."\n\n";

/**/

/*
$property6 = new PropertyList();

$property6->set('-moz-border-radius-topleft', '1em 5em');
echo '#0'."\n";
echo $property6."\n\n";

echo '#1'."\n";
$property6->set('-moz-border-radius-topright', '1em 5em');
echo $property6."\n\n";

echo '#2'."\n";
$property6->set('-moz-border-radius-bottomright', '1em 5em');
echo $property6."\n\n";

echo '#3'."\n";
$property6->set('-moz-border-radius-bottomright', '1em 5em');
echo $property6."\n\n";
*/