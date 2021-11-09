<?php

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

final class Vendor extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider testVendorProvider
     */
    public function testVendor($expected, $actual)
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    public function testVendorProvider() {

        $data = [];

        $data[] = ['body {
 font-family: var(--body-font-family);
 font-size: var(--body-font-size);
 font-weight: var(--body-font-weight);
 line-height: var(--body-line-height);
 color: var(--body-color);
 text-align: var(--body-text-align);
 -webkit-text-size-adjust: 100%
}', (string) (new Parser())->load(__DIR__.'/../var/style.css')];

        return $data;
    }
}

