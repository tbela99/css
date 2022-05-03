#!/usr/bin/php
<?php

use TBela\CSS\Parser;
use \TBela\CSS\Renderer;

require 'autoload.php';

$css = 'table.colortable {
  & td {
    text-align:center;
    &.c { text-transform:uppercase }
    &:first-child, &:first-child + td { border:1px solid black }
  }
  & th {
    text-align:center;
    background:black;
    color:white;
  }
}';

$renderer = new Renderer( ['legacy_rendering' => true]);
echo $renderer->renderAst(new Parser($css));