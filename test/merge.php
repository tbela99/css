#!/usr/bin/php
<?php

require 'autoload.php';

use TBela\CSS\Element\AtRule;
use \TBela\CSS\Renderable;
use \TBela\CSS\Element\Declaration;
use \TBela\CSS\Property\Property;
use \TBela\CSS\Renderer;
use \TBela\CSS\Compiler;
use TBela\CSS\Value;
use TBela\CSS\Value\CSSFunction;

$element = (new Compiler())->setContent('@font-face {
  font-family: "Bitstream Vera Serif Bold", "Arial", "Helvetica";
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff");
}
.pic {
background: no-repeat url("imgs/lizard.png");
}
.element {
background-image: url("imgs/lizard.png"),
                  url("imgs/star.png");
}')->getData();

$renderer = new Renderer();

$renderer->on('traverse', function (Renderable $node) {

    // remove @font-face
    if ($node instanceof AtRule && $node->getName() == 'font-face') {

        return Renderer::REMOVE_NODE;
    }

    // rewrite image url() path for local file
    if ($node instanceof Declaration || $node instanceof Property) {

        if (strpos($node->getValue(), 'url(') !== false) {

            $node = clone $node;

            $node->getValue()->map(function (Value $value): Value {

                if ($value instanceof CSSFunction && $value->name == 'url') {

                    $value->arguments->map(function (Value $value): Value {

                        if (is_file($value->value)) {

                            return Value::getInstance((object) ['type' => $value->type, 'value' => '/'.$value->value]);
                        }

                        return $value;
                    });
                }

                return $value;
            });
        }
    }
});

var_dump($renderer->render($element));
