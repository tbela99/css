<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
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
	 * @dataProvider fromProvider
	 */
	public function testFromProvider(string $expected, string $actual): void
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

    public function sliceProvider (): array
	{

        $data = [];

        $parser = new Parser();

		$size = 64 * 1024;
		$file = __DIR__.'/../perf_files/bs.4';

		$json = [];

		foreach ($parser->slice(css: file_get_contents($file.'.css'), size: $size, position: (object) [
			'line' => 1,
			'column' => 1,
			'index' => 0
		]) as $line) {

			$json[] = $line;
		}

		$data[] = [

			file_get_contents(__DIR__.'/../multiprocessing/bs.4-slice.json'),
			json_encode($json, JSON_PRETTY_PRINT)
		];

		$size = 20;
		$file = __DIR__.'/../nested/nested';

		$json = [];

		foreach ($parser->slice(css: file_get_contents($file.'.css'), size: $size, position: (object) [
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

        foreach ($parser->slice(css: file_get_contents($file.'.min.css'), size: $size, position: (object) [
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
	 * @throws Parser\SyntaxError
	 * @throws Exception
	 */
    public function loadProvider (): array
    {

        $files = [
            __DIR__.'/../nested/nested.css',
            __DIR__.'/../nested/nested.min.css',
            __DIR__.'/../sourcemap/sourcemap.import.css',
            __DIR__.'/../perf_files/bs-mtrl.css',
//            __DIR__.'/../perf_files/bs-reboot.css',
//            __DIR__.'/../perf_files/bs.3.css',
			__DIR__.'/../perf_files/bs.4.css',
//			__DIR__.'/../perf_files/none.css',
//			__DIR__.'/../perf_files/row.css',
//			__DIR__.'/../perf_files/row.min.css',
//			__DIR__.'/../perf_files/main.min.css',
			__DIR__.'/../perf_files/perf.css',
//			__DIR__.'/../perf_files/php-net.css',
//			__DIR__.'/../perf_files/main.min.css',
//			__DIR__.'/../perf_files/uncut.css',
//			__DIR__.'/../perf_files/uncut.css',
//            __DIR__.'/../perf_files/uncut.min.css'
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
	 * @throws Exception
	 */
	public function fromProvider (): array
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

			$data[] = [
				Renderer::fromFile($file, parseOptions: [
					'flatten_import' => true
				]),
				(string) (new Parser(options: [
					'flatten_import' => true
				]))->load($file)
			];
		}

		return $data;
	}

	/**
	 * @throws Parser\SyntaxError
	 * @throws Exception
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

