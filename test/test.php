#!/usr/bin/php
<?php

require 'autoload.php';

use \TBela\CSS\Compiler;
use TBela\CSS\Element\Stylesheet;

$compiler = new Compiler();

$css = '
@font-face {
  font-family: "Bitstream Vera Serif Bold";
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff");
}

body {
  background-color: green;
  color: #fff;
  font-family: Arial, Helvetica, sans-serif;
}
h1 {
  color: #fff;
  font-size: 50px;
  font-family: Arial, Helvetica, sans-serif;
  font-weight: bold;
}

@media print {
  @font-face {
    font-family: MaHelvetica;
    src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"),
      url(MgOpenModernaBold.ttf);
    font-weight: bold;
  }
  body {
    font-family: "Bitstream Vera Serif Bold", serif;
  }
  p {
    font-size: 12px;
    color: #000;
    text-align: left;
  }

  @font-face {
    font-family: Arial, MaHelvetica;
    src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"),
      url(MgOpenModernaBold.ttf);
    font-weight: bold;
  }
}';

$compiler->setContent($css);

$stylesheet = $compiler->getData();

// get all src properties in a @font-face rule
$nodes = $stylesheet->query('@font-face/src');

$stylesheet = new Stylesheet();

foreach($nodes as $node) {

  $stylesheet->append($node->copy());
}

$stylesheet = Stylesheet::getInstance($stylesheet, true);

echo $stylesheet;
