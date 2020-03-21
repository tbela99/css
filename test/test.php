#!/usr/bin/php
<?php

require 'autoload.php';

use \TBela\CSS\Parser;
use \TBela\CSS\Renderer;


$css = '@media print {
  @font-face {
    font-family: Arial, MaHelvetica;
    src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"),
      url(MgOpenModernaBold.ttf);
    font-weight: bold;
  }
}';

$parser = new Parser();
$renderer = new Renderer();

$parser->setContent($css);
$renderer->setOptions(['rgba_hex' => true, 'compress' => true]);

$stylesheet = $parser->parse();

// get @font-face element
$media = $stylesheet['firstChild'];
$fontFace = $media['firstChild'];

echo $renderer->render($fontFace);