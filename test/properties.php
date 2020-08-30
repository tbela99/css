#!/usr/bin/php
<?php

require 'autoload.php';

use \TBela\CSS\Property\PropertyList;

$property = new PropertyList;

$property->set('margin-top', '5px \9');
echo $property."\n\n";

$property->set('margin-left', '5px \9');
echo $property."\n\n";

$property->set('margin-bottom', '5px \9');
echo $property."\n\n";

$property->set('margin-right', '5px \9');
echo $property."\n\n";

echo $property."\n\n";

/**/

/*
$property->set('margin-top', '5px');
$property->set('margin-left', '5px');
$property->set('margin-bottom', '5px');
$property->set('margin-right', '5px');

echo $property."\n\n";

*/
/*
 *
.card-header:first-child {
 border-top-left-radius: calc(.25rem- 1px) calc(.25rem- 1px) 0 0;
 border-top-right-radius: calc(.25rem- 1px) calc(.25rem- 1px) 0 0;
 border-bottom-right-radius: calc(.25rem- 1px) calc(.25rem- 1px) 0 0;
 border-bottom-left-radius: calc(.25rem- 1px) calc(.25rem- 1px) 0 0
}

$property = new PropertyList;
$property->set('border-top-left-radius', 'calc(.25rem- 1px) calc(.25rem- 1px) 0 0');
$property->set('border-top-right-radius', 'calc(.25rem- 1px) calc(.25rem- 1px) 0 0');
$property->set('border-bottom-right-radius', 'calc(.25rem- 1px) calc(.25rem- 1px) 0 0');
$property->set('border-bottom-left-radius', 'calc(.25rem- 1px) calc(.25rem- 1px) 0 0');
echo $property."\n\n";

$property = new PropertyList;
$property->set('border-radius', '10% 17% 10% 17% / 50% 20% 50% 20%');
echo $property."\n\n";

$property = new PropertyList;
$property->set('margin', '5px 3px');
echo $property."\n\n";

$property->set('margin', '0 0 15px 15px');
echo $property."\n\n";

$property->set('margin-left', '15px');
echo $property."\n\n";

$property = new PropertyList;
$property->set('margin', '0 auto');
echo $property."\n\n";

 */