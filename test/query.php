#!/usr/bin/php
<?php

use TBela\CSS\Parser as CssParser;
use TBela\CSS\Query\Parser as QueryParser;

require 'autoload.php';

//$query = 'span[@name="foo"] [@name="bar"]';
//$css = file_get_contents(__DIR__.'/query/style.css');
//
//$parser = new Parser();
//
//echo $parser->parse($query)."\n";
// var_dump($parser->parse($query));

$css = '@font-face {
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

@media print, screen and (max-width: 12450px) {

p {
      color: #f0f0f0;
      background-color: #030303;
  }
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
    src: url(MgOpenModernaBold.ttf), local("Helvetica Neue Bold"), local("HelveticaNeue-Bold")
      ;
    font-weight: bold;
  }
}';

$query = '@font-face/src/..|html, body[@name="foo bar"] | span[@name="foo"] [@name="bar"] |.nav, @media, p:before/content/.. | body
,header';
$query = '// @font-face / src / .. | @media[@value^=print][1],p';

//echo (new QueryParser())->parse($query);

echo implode("\n", (new CssParser($css))->parse()->query($query));