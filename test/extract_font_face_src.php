#!/usr/bin/php
<?php

require 'autoload.php';

use \TBela\CSS\Parser;
use \TBela\CSS\Element;
use \TBela\CSS\ElementAtRule;
use \TBela\CSS\ElementStylesheet;

$parser = new Parser('./css/manipulate.css', [
    'silent' => false,
    'flatten_import' => true
]);

$stylesheet = new ElementStylesheet();

function getNodes ($data, $stylesheet) {

    $nodes = [];

    foreach ($data as $node) {

        if ($node instanceof ElementAtRule) {

            switch ($node->getName()) {

                case 'font-face':

                    foreach ($node as $declaration) {

                        if ($declaration['name'] == 'src') {

                            $stylesheet->append($declaration->copy()->getRoot());
                            break;
                        }
                    }

                    break;

                case 'media':

                    getNodes($node, $stylesheet);
                    break;
            }
        }
    }
}

getNodes ($parser->parse(), $stylesheet);

// deduplicate rules
$stylesheet = Element::getInstance($parser->deduplicate($stylesheet));

echo $stylesheet;

file_put_contents('output/extract_font_face_src.css', $stylesheet);