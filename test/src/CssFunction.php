<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

final class CssFunction extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider cssFunctionProvider
     */
    public function testCssFunction($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
          $actual
        );
    }


    public function cssFunctionProvider() {

        $parser = new Parser('.a { --theme-primary-350:rgb(calc(51 + var(--theme-primary-color-r) * .8), calc(51 + var(--theme-primary-color-g) * .8), calc(51 + var(--theme-primary-color-b) * .8));}');

        $data = [];

        $data[] = ['.a {
 --theme-primary-350: rgb(calc(51 + var(--theme-primary-color-r) * .8), calc(51 + var(--theme-primary-color-g) * .8), calc(51 + var(--theme-primary-color-b) * .8))
}', (string) $parser];

        $data[] = [ '.a{--theme-primary-350:rgb(calc(51 + var(--theme-primary-color-r) * .8),calc(51 + var(--theme-primary-color-g) * .8),calc(51 + var(--theme-primary-color-b) * .8))}', (new Renderer(['compress' => true]))->renderAst($parser) ];

        return $data;
    }
}

