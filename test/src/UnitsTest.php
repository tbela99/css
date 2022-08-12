<?php

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;

require_once __DIR__.'/../bootstrap.php';

final class UnitsTest extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider zeroProvider
	 * @small
     */
    public function testZero($expected, $actual)
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    public function zeroProvider()
    {


        $parser = new Parser('
@media (min-width: 0dpi) {

textarea {
  border: 0px;
  padding: 10px 0px 12px 35px;
  transition: all 0ms ease-in-out;
  foo: 0hz 0khz;
  bar: 0x 0dpcm 0dppx 0dpi 0s 0ms;
}
}');

        return [
            [

                '@media (min-width:0dpi) {
 textarea {
  border: 0;
  padding: 10px 0 12px 35px;
  transition: all 0s ease-in-out;
  foo: 0hz 0hz;
  bar: 0x 0dpcm 0x 0dpi 0s 0s
 }
}',
                (string) $parser
            ],
            [

            '@media(min-width:0dpi){textarea{border:0;padding:10px 0 12px 35px;transition:all 0s ease-in-out;foo:0hz 0hz;bar:0x 0dpcm 0x 0dpi 0s 0s}}',
                (new \TBela\CSS\Renderer(['compress' => true]))->renderAst($parser)
            ]
        ];

    }
}

