#!/usr/bin/php
<?php

require 'autoload.php';

use \TBela\CSS\Parser;
use \TBela\CSS\Element;
use \TBela\CSS\Element\AtRule;
use \TBela\CSS\Element\Stylesheet;

$parser = new Parser('./css/manipulate.css', [
    'silent' => false,
    'flatten_import' => true
]);

$stylesheet = new Stylesheet();

function getNodes (Element $data, $stylesheet) {

    foreach ($data['children'] as $node) {

        if ($node instanceof AtRule) {

            switch ($node['name']) {

                case 'font-face':

                    foreach ($node['children'] as $declaration) {

                        if ($declaration['name'] == 'src') {

                            //   var_dump($declaration->copy() == $declaration);
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