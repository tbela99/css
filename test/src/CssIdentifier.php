<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

final class CssIdentifier extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider identifierProvider
     */
    public function testIdentifier($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
          $actual
        );
    }


    public function identifierProvider() {

        $data = [];

        $data[] = [(string) (new Parser('div[data-elem-id="1587819236980"]{
background:red;
}'))->parse(), 'div[data-elem-id="1587819236980"] {
 background: red
}'];

        return $data;
    }
}

