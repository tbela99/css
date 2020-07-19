<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

final class Reparse extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider test1Provider
     */
    public function test1($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
          $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider test2Provider
     */
    public function test2($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider test3Provider
     */
    public function test3($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider test4Provider
     */
    public function test4($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider test5Provider
     */
    public function test5($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider test6Provider
     */
    public function test6($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider test7Provider
     */
    public function test7($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    public function test1Provider() {

        return $this->makeTest(1);
    }

    public function test2Provider() {

        return $this->makeTest(2);
    }

    public function test3Provider() {

        return $this->makeTest(3);
    }

    public function test4Provider() {

        return $this->makeTest(4);
    }

    public function test5Provider() {

        return $this->makeTest(5);
    }

    public function test6Provider() {

        return $this->makeTest(6);
    }

    public function test7Provider() {

        return $this->makeTest(7);
    }

    public function makeTest (int $index) {

        $data = [];

        $renderer = new Renderer();
        $parser = new Parser();

        $parser->setOptions(['sourcemap' => true, 'flatten_import' => true])->load(__DIR__.'/../files/test_'.$index.'.css');

        $data[] = [

            file_get_contents(__DIR__.'/../files/test_'.$index.'_parsed_comments.css'),
            $renderer->render($parser->parse())
        ];

        $data[] = [

            file_get_contents(__DIR__.'/../files/test_'.$index.'_sourcemap.json'),
            json_encode($parser->parse(), JSON_PRETTY_PRINT)
        ];

        $data[] = [

            file_get_contents(__DIR__.'/../files/test_'.$index.'_parsed.css'),
            $renderer->setOptions(['remove_comments' => true])->render($parser->parse())
        ];

        $data[] = [

            file_get_contents(__DIR__.'/../files/test_'.$index.'_parsed.min.css'),
            $renderer->setOptions(['compress' => true])->render($parser->parse())
        ];

        $data[] = [

            file_get_contents(__DIR__.'/../files/test_'.$index.'_no_sourcemap.json'),
            json_encode($parser->setOptions(['sourcemap' => false])->parse(), JSON_PRETTY_PRINT)
        ];

        $renderer = new Renderer();
        $parser = new Parser();

        $parser->setOptions(['sourcemap' => true])->setContent(file_get_contents(__DIR__.'/../files/test_'.$index.'_parsed.min.css'));

        $data[] = [

            file_get_contents(__DIR__.'/../files/test_'.$index.'_parsed.css'),
            $renderer->render($parser->parse())
        ];

        return $data;
    }
}

