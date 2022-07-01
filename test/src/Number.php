<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Event\Event as EventTest;

final class Number extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider testNumberProvider
     */
    public function testNumber(string $expected, string $actual): void
    {

        $this->assertEquals(
            $expected,
          $actual
        );
    }

/*
*/
    public function testNumberProvider () {

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

        return $data;
    }
}

