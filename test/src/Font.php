<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Element;
use TBela\CSS\Compiler;
use TBela\CSS\Parser;

final class Font extends TestCase
{
    /**
     * @param Element $element
     * @param $jsonData
     * @param string $expected
     * @dataProvider fontWeightProvider
     */
    public function testFontWeight(Element $element, $jsonData, $expected): void
    {

        $this->assertEquals(
            $jsonData,
           json_encode($element)
        );

        $this->assertEquals(
            (string) $element,
            $expected
        );
    }

/*
*/
    public function fontWeightProvider () {

        $data = [];

        $data = new Compiler(['compress' => true]);

        $data[] = [$data->setContent('
strong {

font-weight: Extra Black;
}
')->compile(), ''];

        return $data;
    }
}

