#!/usr/bin/php
<?php

require 'autoload.php';

$parser =  (new \TBela\CSS\Parser("@charset \"utf-8\"; @font-face{font-family:'CenturyGothic';src:url('/CenturyGothic.woff') format('woff');font-weight:400;}", ['capture_errors' => false])) /* ->load('template.min.css') */;

echo (new \TBela\CSS\Renderer(['charset' => true]))->renderAst($parser);