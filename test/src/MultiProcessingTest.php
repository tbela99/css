<?php

use PHPUnit\Framework\TestCase;
use TBela\CSS\Event\Event as EventTest;
use TBela\CSS\Exceptions\IOException;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

final class MultiProcessingTest extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider sliceProvider
     */
    public function testSlice($expected, $actual)
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
    public function testLoad($expected, $actual)
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

	/**
	 * @param string $expected
	 * @param string $actual
	 * @dataProvider fromProvider
	 */
	public function testFromProvider($expected, $actual)
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
    public function testAst($expected, $actual)
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

        foreach ($parser->slice(file_get_contents($file.'.css'), $size, (object) [
            'line' => 1,
            'column' => 1,
            'index' => 0
        ]) as $line) {

            $json[] = $line;
        }

        $data[] = [

            file_get_contents(__DIR__.'/../multiprocessing/nested-slice.json'),
            json_encode($json, JSON_PRETTY_PRINT)
        ];

        $json = [];

        foreach ($parser->slice(file_get_contents($file.'.min.css'), $size, (object) [
            'line' => 1,
            'column' => 1,
            'index' => 0
        ]) as $line) {

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
    public function loadProvider ()
    {

        $files = [
            __DIR__.'/../nested/nested.css',
            __DIR__.'/../nested/nested.min.css',
            __DIR__.'/../sourcemap/sourcemap.import.css',
            __DIR__.'/../perf_files/bs-mtrl.css',
            __DIR__.'/../perf_files/bs-reboot.css',
            __DIR__.'/../perf_files/bs.3.css',
			__DIR__.'/../perf_files/bs.4.css',
			__DIR__.'/../perf_files/none.css',
			__DIR__.'/../perf_files/row.css',
			__DIR__.'/../perf_files/row.min.css',
			__DIR__.'/../perf_files/main.min.css',
			__DIR__.'/../perf_files/perf.css',
			__DIR__.'/../perf_files/php-net.css',
			__DIR__.'/../perf_files/main.min.css',
			__DIR__.'/../perf_files/uncut.css',
			__DIR__.'/../perf_files/uncut.css',
            __DIR__.'/../perf_files/uncut.min.css'
        ];


        $data = [];

        foreach ($files as $file) {

            $content = file_get_contents($file);

            $data[] = [
                (string) (new Parser('', [
                    'multi_processing' => false,
                    'flatten_import' => true
                ]))->append($file),
                (string) (new Parser('', [
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
	public function fromProvider ()
	{

		$files = [
			__DIR__.'/../nested/nested.css',
			__DIR__.'/../nested/nested.min.css',
			__DIR__.'/../sourcemap/sourcemap.import.css',
			__DIR__.'/../perf_files/bs-mtrl.css',
			__DIR__.'/../perf_files/bs-reboot.css',
			__DIR__.'/../perf_files/bs.3.css',
			__DIR__.'/../perf_files/bs.4.css',
			__DIR__.'/../perf_files/none.css',
			__DIR__.'/../perf_files/row.css',
			__DIR__.'/../perf_files/row.min.css',
			__DIR__.'/../perf_files/main.min.css',
			__DIR__.'/../perf_files/perf.css',
			__DIR__.'/../perf_files/php-net.css',
			__DIR__.'/../perf_files/main.min.css',
			__DIR__.'/../perf_files/uncut.css',
			__DIR__.'/../perf_files/uncut.css',
			__DIR__.'/../perf_files/uncut.min.css'
		];


		$data = [];

		foreach ($files as $file) {

//			$content = file_get_contents($file);

			$data[] = [
				Renderer::fromFile($file, [], [
					'flatten_import' => true
				]),
				(string) (new Parser('', [
					'flatten_import' => true
				]))->load($file)
			];
		}

		return $data;
	}

    /**
     * @throws Parser\SyntaxError
	 */
    public function astProvider ()
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
                json_encode((new Parser('', [
                    'multi_processing' => false,
                    'flatten_import' => true
                ]))->append($file)->getAst(), JSON_PRETTY_PRINT),
                json_encode((new Parser('', [
                    'ast_src' => $file,
                    'flatten_import' => true
                ]))->appendContent($content)->getAst(), JSON_PRETTY_PRINT)

            ];
        }

        return $data;
    }
}

