#!/usr/bin/php
<?php

set_time_limit(1);

require 'autoload.php';

use \TBela\CSS\Parser;
use \TBela\CSS\Element;
use \TBela\CSS\ElementStylesheet;
use \TBela\CSS\ElementAtRule;
use \TBela\CSS\Identity;

$renderer = new Identity();
$parser = new Parser(file_get_contents('./css/manipulate.css'), [
    'silent' => false,
    'flatten_import' => true
]);

$stylesheet = new ElementStylesheet();

function getNodes ($data, $stylesheet) {

    $stack = [$data];

    while ($current = array_shift($stack)) {

        foreach ($current as $node) {

            if ($node instanceof ElementAtRule) {

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
}

getNodes ($parser->parse(), $stylesheet);

//deduplicate rules
$stylesheet = Element::getInstance($parser->deduplicate($stylesheet));

echo $stylesheet;

file_put_contents('output/extract_font_face.css', $stylesheet);