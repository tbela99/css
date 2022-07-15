#!/usr/bin/php
<?php

use TBela\CSS\Parser;
use \TBela\CSS\Renderer;

require 'autoload.php';

//$proertyList = new \TBela\CSS\Property\PropertyList();
//
//$proertyList->set('margin', '2px !important');
//$proertyList->set('margin-left', '3px !important');
//
//
//echo $proertyList;

//$parser = new Parser('
//.wp-block-gallery.has-nested-images.columns-default figure.wp-block-image:not(#individual-image):first-child:nth-last-child(2),
//.wp-block-gallery.has-nested-images.columns-default figure.wp-block-image:not(#individual-image):first-child:nth-last-child(2)~figure.wp-block-image:not(#individual-image) {
//  width: calc(50% - var(--wp--style--unstable-gallery-gap, 16px)*0.5)
//}
//');

$parser = new Parser('.cb + .a~.b.cd[type~="ab cd"] {

column-count: 1000;
counter-increment: 2000;
counter-reset: 1000;
grid-column: 1000; 
grid-row: 1000;
 z-index: 10000;
 line-height: 1000;
}');

echo (new Renderer(['compress' => true]))->renderAst($parser);