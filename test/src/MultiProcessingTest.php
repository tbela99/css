<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Event\Event as EventTest;
use TBela\CSS\Exceptions\IOException;
use TBela\CSS\Parser;

final class MultiProcessingTest extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider sliceProvider
     */
    public function testSlice(string $expected, string $actual): void
    {

        $this->assertEquals(
            $expected,
          $actual
        );
    }
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider loadProvider
     */
    public function testLoad(string $expected, string $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider astProvider
     */
    public function testAst(string $expected, string $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    public function sliceProvider () {

        $data = [];

        $parser = new Parser();

        $size = 20;
        $file = __DIR__.'/../nested/nested';

        $json = [];

        foreach ($parser->slice(file_get_contents($file.'.css'), (object) [
            'line' => 1,
            'column' => 1,
            'index' => 0
        ], $size) as $line) {

            $json[] = $line;
        }

        $data[] = [

            file_get_contents(__DIR__.'/../multiprocessing/nested-slice.json'),
            json_encode($json, JSON_PRETTY_PRINT)
        ];

        $json = [];

        foreach ($parser->slice(file_get_contents($file.'.min.css'), (object) [
            'line' => 1,
            'column' => 1,
            'index' => 0
        ], $size) as $line) {

            $json[] = $line;
        }

        $data[] = [

            file_get_contents(__DIR__.'/../multiprocessing/nested-slice.min.json'),
            json_encode($json, JSON_PRETTY_PRINT)
        ];

        return $data;
    }

    /**
     * @throws IOException
     * @throws Parser\SyntaxError
     */
    public function loadProvider (): array
    {

        $files = [
            __DIR__.'/../nested/nested.css',
            __DIR__.'/../nested/nested.min.css',
            __DIR__.'/../sourcemap/sourcemap.import.css',
            __DIR__.'/../perf_files/row.css',
            __DIR__.'/../perf_files/row.min.css',
            __DIR__.'/../perf_files/main.min.css',
            __DIR__.'/../perf_files/uncut.css',
            __DIR__.'/../perf_files/uncut.min.css'
        ];


        $data = [];

        foreach ($files as $file) {

            $content = file_get_contents($file);

            $data[] = [
                (string) (new Parser(options: [
                    'multi_processing' => false,
                    'flatten_import' => true
                ]))->append($file),
                (string) (new Parser(options: [
                    'ast_src' => $file,
                    'flatten_import' => true
                ]))->appendContent($content)

            ];
        }

        return $data;
    }

    /**
     * @throws Parser\SyntaxError
     * @throws IOException
     */
    public function astProvider (): array
    {

        $files = [
            __DIR__.'/../nested/nested.css',
            __DIR__.'/../nested/nested.min.css',
            __DIR__.'/../sourcemap/sourcemap.import.css',
            // too large for phpunit to handle
//            __DIR__.'/../perf_files/row.css',
//            __DIR__.'/../perf_files/row.min.css'
        ];


        $data = [];

        foreach ($files as $file) {

            $content = file_get_contents($file);

            $data[] = [
                json_encode((new Parser(options: [
                    'multi_processing' => false,
                    'flatten_import' => true
                ]))->append($file)->getAst(), JSON_PRETTY_PRINT),
                json_encode((new Parser(options: [
                    'ast_src' => $file,
                    'flatten_import' => true
                ]))->appendContent($content)->getAst(), JSON_PRETTY_PRINT)

            ];
        }

        return $data;
    }
}
