#!/usr/bin/php
<?php

set_time_limit(1);

require 'autoload.php';

use TBela\CSS\Element;
use TBela\CSS\Element\AtRule;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;
use TBela\CSS\Element\Stylesheet;

$renderer = new Renderer();
$parser = new Parser('./css/manipulate.css', [
    'silent' => false,
    'flatten_import' => true
]);

$stylesheet = new Stylesheet();

function getNodes ($data, $stylesheet) {

        foreach ($data as $node) {

            if ($node instanceof AtRule) {

                switch ($node->getName()) {

                    case 'font-face':

                        $stylesheet->append($node->copy()->getRoot());
                        break;

                    case 'media':

                        getNodes($node, $stylesheet);
                        break;
                }
            }
        }
}

getNodes ($parser->parse(), $stylesheet);

//deduplicate rules
$stylesheet = Element::getInstance($parser->deduplicate($stylesheet));

echo $stylesheet;

file_put_contents('output/extract_font_face.css', $stylesheet);