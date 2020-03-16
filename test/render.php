#!/usr/bin/php
<?php

require 'autoload.php';

$css = '@media print {
 @font-face {
   font-family: Arial, MaHelvetica;
   src: local("Helvetica Neue Bold"),
        local("HelveticaNeue-Bold"),
        url(MgOpenModernaBold.ttf);
   font-weight: bold
 }
}
';

use \TBela\CSS\Parser;
use \TBela\CSS\Identity;
use \TBela\CSS\Compress;

$parser = new Parser();
$renderer = new Identity();
$compressor = new Compress();

$parser->setContent($css);

$stylesheet = $parser->parse();

// get @font-face element
$media = $stylesheet['children'][0];
$fontFace = $media['children'][0];

echo $renderer->render($fontFace);

echo "\n\n\n";

echo $renderer->render($fontFace, null, true);

echo "\n\n\n";

echo $compressor->render($fontFace);

echo "\n\n\n";

echo $compressor->render($fontFace, null, true);
