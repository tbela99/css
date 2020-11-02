<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Compiler;
use TBela\CSS\Element\AtRule;
use TBela\CSS\Element\Declaration;
use TBela\CSS\Renderer as RendererClass;
use TBela\CSS\Interfaces\RenderableInterface;
use TBela\CSS\Traverser;
use TBela\CSS\Value;
use TBela\CSS\Value\CSSFunction;

final class Renderer extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider testProvider
     */
    public function test($expected, $actual): void
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

        $renderer->on('traverse', function (RenderableInterface $node) {

            // remove @font-face
            if ($node instanceof AtRule && $node->getName() == 'font-face') {

                return Traverser::IGNORE_NODE;
            }

            // rewrite image url() path for local file
            if ($node instanceof Declaration || $node instanceof PropertyInterface) {

                if (strpos((string) $node->getValue(), 'url(') !== false) {

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

            return $node;
        });

        $data = [];

        $data[] = [".pic {
 background: no-repeat url(/imgs/lizard.png)
}
.element {
 background-image: url(/imgs/lizard.png), url(/imgs/star.png)
}",
            $renderer->render($element)];

        return $data;
    }
}