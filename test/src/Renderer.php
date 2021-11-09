<?php

use PHPUnit\Framework\TestCase;
use TBela\CSS\Ast\Traverser;
use TBela\CSS\Compiler;
use TBela\CSS\Element\AtRule;
use TBela\CSS\Element\Declaration;
use TBela\CSS\Property\PropertyList;
use TBela\CSS\Renderer as RendererClass;
use TBela\CSS\Interfaces\ObjectInterface;
use TBela\CSS\Value;
use TBela\CSS\Value\CSSFunction;

final class Renderer extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider testProvider
     */
    public function test($expected, $actual)
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /*
    */
    public function testProvider () {

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

        $renderer = new RendererClass();
        $traverser = new Traverser();

        $traverser->on('enter', function (ObjectInterface $node) {

            // remove @font-face
            if ($node instanceof AtRule && $node->getName() == 'font-face') {

                return Traverser::IGNORE_NODE;
            }

            // rewrite image url() path for local file
            if ($node instanceof Declaration || $node instanceof PropertyList) {

                if (strpos((string) $node->getValue(), 'url(') !== false) {

                    $node = clone $node;

                    $node->getValue()->map(function (Value $value) {

                        if ($value instanceof CSSFunction && $value->name == 'url') {

                            $value->arguments->map(function (Value $value) {

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

            return $node;
        });

        $data = [];

        $data[] = [".pic {
 background: no-repeat url(/imgs/lizard.png)
}
.element {
 background-image: url(/imgs/lizard.png), url(/imgs/star.png)
}",
            $renderer->render($traverser->traverse($element))];

        return $data;
    }
}