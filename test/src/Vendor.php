<?php
declare(strict_types=1);

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
    public function testVendor($expected, $actual): void
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

        $data[] = ['body {
 box-shadow: inset 0 0 0 9999px var(--table-accent-bg)
}
.box {
 -webkit-box-shadow: 0 0 0 .25rem rgba(var(--cassiopeia-color-primary), .25)
}', (string) (new Parser())->load(__DIR__.'/../var/var.css')];

        $data[] = ['.site-grid>[class*=" container-"],
.site-grid>[class^=container-] {
 -webkit-column-gap: 1em;
 -moz-column-gap: 1em;
 column-gap: 1em;
 max-width: none;
 width: 100%
}', (string) new Parser('.site-grid>[class*=" container-"], .site-grid>[class^=container-] {
    -webkit-column-gap: 1em;
    -moz-column-gap: 1em;
    column-gap: 1em;
    max-width: none;
    width: 100%;
}
')];

        return $data;
    }
}

