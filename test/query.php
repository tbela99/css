#!/usr/bin/php
<?php

use TBela\CSS\Compiler;
use TBela\CSS\Parser as CssParser;
use TBela\CSS\Property\PropertyList;
use TBela\CSS\Query\Parser as QueryParser;

require 'autoload.php';
//
//echo (new QueryParser())->parse(' [ data-catalyst ] ');
//die;

//$query = 'span|div,span|div, .div| span *, nav| *, .jdb-button-link,#jde-q2150115467813748jk .jdb-button-link, *[class*=jdb-container]';

//$query = file_get_contents(__DIR__.'/query.txt');
//$element = (new CssParser())->load(__DIR__.'/perf_files/main.min.css')->parse();

$css = '
*[class*=jdb-container], *[class*=jdb-container] *, *[class*=jdb-container]::before {
    box-sizing: border-box
}

.jdb-container {
    width: 100%;
    padding-right: 15px;
    padding-left: 15px;
    margin-right: auto;
    margin-left: auto
}

@media (min-width: 576px) {
    .jdb-container {
        max-width: 540px
    }
}

@media (min-width: 768px) {
    .jdb-container {
        max-width: 720px
    }
}

@media (min-width: 992px) {
    .jdb-container {
        max-width: 960px
    }
}

@media (min-width: 1200px) {
    .jdb-container {
        max-width: 1140px
    }
}

.jdb-container-fluid, .jdb-container-sm, .jdb-container-md, .jdb-container-lg, .jdb-container-xl {
    width: 100%;
    padding-right: 15px;
    padding-left: 15px;
    margin-right: auto;
    margin-left: auto
}

#outdated {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 170px;
    text-align: center;
    text-transform: uppercase;
    z-index: 1500;
    background-color: #f25648;
    color: #fff
}

* html #outdated {
    position: absolute
}

#outdated h6 {
    font-size: 25px;
    line-height: 25px;
    margin: 30px 0 10px
}

#outdated p {
    font-size: 12px;
    line-height: 12px;
    margin: 0
}

#outdated #btnUpdateBrowser {
    display: block;
    position: relative;
    padding: 10px 20px;
    margin: 30px auto 0;
    width: 230px;
    color: #fff;
    text-decoration: none;
    border: 2px solid #fff;
    cursor: pointer
}

input[type="text"]
{
background:red;
}


@font-face {
  font-family: "Bitstream Vera Serif Bold";
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff");
}

';

$query = '@font-face, input[type="text"], * html #outdated, .jdb-container-xl ';

$element = (new CssParser($css))->parse();



echo var_export(array_map('trim', $element->queryByClassNames($query)), true);
//echo $element;