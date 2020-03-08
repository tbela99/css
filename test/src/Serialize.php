<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Element;
use TBela\CSS\Element\AtRule;
use TBela\CSS\Element\Stylesheet;
use TBela\CSS\Compiler;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

// because git changes \n to \r\n at some point, this causes test failure
function get_content($file) {

    return file_get_contents($file);
}


final class Serialize extends TestCase
{
    /**
     * @param Parser $parser
     * @param Compiler $compiler
     * @param string $file
     * @param string $expected
     * @throws Exception
     * @dataProvider serializeProvider
     */
    public function testSerialize(Element $element, $jsonData, $expected): void
    {

        $this->assertEquals(
           json_encode($element),
            $jsonData
        );

        $this->assertEquals(
            (string) $element,
            get_content($expected)
        );
    }

/*
*/
    public function serializeProvider () {

        $jsonData = file_get_contents(__DIR__.'/../output/atrules.json');

        return [
            [Element::getInstance(json_decode($jsonData)), $jsonData, __DIR__.'/../output/atrules.css']
        ];
    }
}

