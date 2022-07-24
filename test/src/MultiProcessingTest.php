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
    public function loadProvider () {

        $threshold = 20;
        $file = __DIR__.'/../nested/nested.css';
        $content = file_get_contents($file);

        $data = [];

        $data[] = [

            (string) (new Parser())->load($file),
            (string) (new Parser(options: ['multi_processing_threshold' => $threshold]))->load($file)
        ];

        $data[] = [

            (string) (new Parser())->append($file),
            (string) (new Parser(options: ['multi_processing_threshold' => $threshold]))->append($file)
        ];

        $data[] = [

            (string) (new Parser())->appendContent($content),
            (string) (new Parser(options: ['multi_processing_threshold' => $threshold]))->appendContent($content)
        ];

        $file = __DIR__.'/../sourcemap/sourcemap.import.css';
        $options = ['flatten_import' => true, 'multi_processing_threshold' => $threshold, 'ast_src' => $file];
        $content = file_get_contents($file);

        $data[] = [

            (string) (new Parser(options: ['flatten_import' => true]))->load($file),
            (string) (new Parser(options: $options))->load($file)
        ];

        $data[] = [

            (string) (new Parser(options: ['flatten_import' => true]))->append($file),
            (string) (new Parser(options: $options))->append($file)
        ];

        $data[] = [

            (string) (new Parser(options: ['flatten_import' => true, 'ast_src' => $file]))->appendContent($content),
            (string) (new Parser(options: $options))->appendContent($content)
        ];

        return $data;
    }

    /**
     * @throws Parser\SyntaxError
     * @throws IOException
     */
    public function astProvider () {

        $threshold = 20;
        $file = __DIR__.'/../nested/nested.css';
        $content = file_get_contents($file);

        $data = [];

        $data[] = [

            json_encode((new Parser())->load($file)->getAst(), JSON_PRETTY_PRINT),
            json_encode((new Parser(options: ['multi_processing_threshold' => $threshold]))->load($file)->getAst(), JSON_PRETTY_PRINT)
        ];

        $data[] = [

            json_encode((new Parser())->append($file), JSON_PRETTY_PRINT),
                json_encode((new Parser(options: ['multi_processing_threshold' => $threshold]))->append($file), JSON_PRETTY_PRINT)
        ];

        $data[] = [

            json_encode((new Parser())->appendContent($content), JSON_PRETTY_PRINT),
                json_encode((new Parser(options: ['multi_processing_threshold' => $threshold]))->appendContent($content), JSON_PRETTY_PRINT)
        ];

        $file = __DIR__.'/../sourcemap/sourcemap.import.css';
        $options = ['flatten_import' => true, 'multi_processing_threshold' => $threshold, 'ast_src' => $file];
        $content = file_get_contents($file);

        $data[] = [

            json_encode((new Parser(options: ['flatten_import' => true]))->load($file)->getAst(), JSON_PRETTY_PRINT),
            json_encode((new Parser(options: $options))->load($file)->getAst(), JSON_PRETTY_PRINT)
        ];

        $data[] = [

            json_encode((new Parser(options: ['flatten_import' => true]))->append($file), JSON_PRETTY_PRINT),
            json_encode((new Parser(options: $options))->append($file), JSON_PRETTY_PRINT)
        ];

        $data[] = [

            json_encode((new Parser(options: ['flatten_import' => true, 'ast_src' => $file]))->appendContent($content), JSON_PRETTY_PRINT),
            json_encode((new Parser(options: $options))->appendContent($content), JSON_PRETTY_PRINT)
        ];

        return $data;
    }
}

