#!/usr/bin/php
<?php

use TBela\CSS\Element;

require 'autoload.php';
$css = '


@font-face {
 font-family: "Font Awesome 5 Brands";
 font-style: normal;
 font-weight: 400;
 font-display: block;
 src: url(components/com_sppagebuilder/assets/webfonts/fa-brands-400.woff) format("woff"), url(components/com_sppagebuilder/assets/webfonts/fa-brands-400.ttf) format("truetype")
}
.fab {
 font-family: "Font Awesome 5 Brands"
}
@font-face {
 font-family: "Font Awesome 5 Free";
 font-style: normal;
 font-weight: 400;
 font-display: block;
 src: url(components/com_sppagebuilder/assets/webfonts/fa-regular-400.woff) format("woff"), url(components/com_sppagebuilder/assets/webfonts/fa-regular-400.ttf) format("truetype")
}
.fab,
.far {
 font-weight: 400
}
@font-face {
 font-family: "Font Awesome 5 Free";
 font-style: normal;
 font-weight: 900;
 font-display: block;
 src: url(components/com_sppagebuilder/assets/webfonts/fa-solid-900.woff) format("woff"), url(components/com_sppagebuilder/assets/webfonts/fa-solid-900.ttf) format("truetype")
}
';


$context = '// @font-face / src / ..';

$element = Element::from($css);

foreach ($element->query('[@name=background][@value*="url("]|[@name=background-image][@value*="url("]|[@name=src][@value*="url("]') as $p) {

    $values = [];

    foreach($p->getRawValue() as $i => $img) {

        if ($img->type == 'css-url' && isset($img->arguments[0]->value)) {

            echo $img->type.' => '.$img->arguments[0]->value."\n";

//            var_dump($img->arguments[0]->value);
//            $img->arguments[0]->value = '/img/bg'.$i.'.png';
        }

//        $values[] = $img;
    }

//    $p->setValue($values);
}

//echo "$element\n";