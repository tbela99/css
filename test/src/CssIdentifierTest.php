<?php

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

final class CssIdentifierTest extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider identifierProvider
     */
    public function testIdentifier($expected, $actual)
    {

        $this->assertEquals(
            $expected,
          $actual
        );
    }


    public function identifierProvider() {

        $data = [];

        $data[] = ['div[data-elem-id="1587819236980"] {
 background: red
}', (string) (new Parser('div[data-elem-id="1587819236980"]{
background:red;
}'))->parse()];

        $data[] = [ 'div[data-elem-id=a1587819236980] {
 background: red
}', (string) (new Parser('div[data-elem-id="a1587819236980"]{
background:red;
}'))->parse()];

        return $data;
    }

    public function identifierAstProvider() {

        $data = [];

        $data[] = ['div[data-elem-id="1587819236980"] {
 background: red
}', (new Renderer())->renderAst((new Parser('div[data-elem-id="1587819236980"]{
background:red;
}'))->getAst()) ];

        $data[] = [ 'div[data-elem-id=a1587819236980] {
 background: red
}', (new Renderer())->renderAst((new Parser('div[data-elem-id="a1587819236980"]{
background:red;
}'))->getAst()) ];

        return $data;
    }
}

