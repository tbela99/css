<?php

use PHPUnit\Framework\TestCase;
use TBela\CSS\Event\Event as EventTest;
use TBela\CSS\Parser;

require_once __DIR__.'/../bootstrap.php';

final class NumberTest extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider numberProvider
     */
    public function testNumber($expected, $actual)
    {

        $this->assertEquals(
            $expected,
          $actual
        );
    }

/*
*/
    public function numberProvider () {

        $parser = new TBela\CSS\Parser('.row {
 --bs-gutter-x: 1.5rem;
 --bs-gutter-y: 0;
 display: flex;
 flex-wrap: wrap;
 margin-top: calc(-1 * var(--bs-gutter-y));
 margin-right: calc(-.5 * var(--bs-gutter-x)); 
 margin-left: calc(-.5 * var(--bs-gutter-x))
}');

        $renderer = new \TBela\CSS\Renderer();

        $data = [];

        $data[] = [

            '.row {
 --bs-gutter-x: 1.5rem;
 --bs-gutter-y: 0;
 display: flex;
 flex-wrap: wrap;
 margin-top: calc(-1 * var(--bs-gutter-y));
 margin-right: calc(-.5 * var(--bs-gutter-x));
 margin-left: calc(-.5 * var(--bs-gutter-x))
}',
            $renderer->renderAst($parser)
        ];

        $renderer->setOptions(['compress' => true]);

        $data[] = [

            '.row{--bs-gutter-x:1.5rem;--bs-gutter-y:0;display:flex;flex-wrap:wrap;margin-top:calc(-1 * var(--bs-gutter-y));margin-right:calc(-.5 * var(--bs-gutter-x));margin-left:calc(-.5 * var(--bs-gutter-x))}',
            $renderer->renderAst($parser)
        ];

        $data[] = [

            '.cb+.a~.b.cd[type~="ab cd"]{column-count:1000;counter-increment:2000;counter-reset:1000;grid-column:1000;grid-row:1000;z-index:10000;line-height:1e3}',
            $renderer->renderAst(new Parser('.cb + .a~.b.cd[type~="ab cd"] {

column-count: 1000;
counter-increment: 2000;
counter-reset: 1000;
grid-column: 1000; 
grid-row: 1000;
 z-index: 10000;
 line-height: 1000;
}'))
        ];

        return $data;
    }
}

