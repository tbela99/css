#!/usr/bin/php
<?php

use TBela\CSS\Element;

require 'autoload.php';
$css = '

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
    src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local("HelveticaNeue-Bold")
      ;
    font-weight: bold;
  }
}';


$context = '// @font-face / src / ..';

$element = Element::fromUrl('https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css');

foreach ($element->query('[@name=background][@value*="url("]|[@name=background-image][@value*="url("]') as $p) {

//    var_dump($p->getRawValue());
    echo "$p\n";
}
