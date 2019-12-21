<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Compiler;
use TBela\CSS\Parser;


final class DuplicateTest extends TestCase
{
    /**
     * @param Parser $parser
     * @param Compiler $compiler
     * @param $css
     * @throws Exception
     * @dataProvider beautifyProvider
     */
    public function testBeautifyDuplicate(Parser $parser, Compiler $compiler, $file, $expected): void
    {

        $compiler->setData($parser->load($file)->parse());

        $this->assertEquals(
           $compiler->compile(),
            $expected
        );
    }

    /**
     * @param Parser $parser
     * @param Compiler $compiler
     * @param $css
     * @throws Exception
     * @dataProvider minifiedProvider
     */
    public function testMinifyDuplicate(Parser $parser, Compiler $compiler, $file, $expected): void
    {

        $compiler->setData($parser->load($file)->parse());

        $this->assertEquals(
            $compiler->compile(),
            $expected
        );
    }

    public function beautifyProvider(): array
    {

        $data = [];

        foreach (glob('css/*.css') as $file) {

            $data[] = [

                (new Parser())->setOptions(['flatten_import' => true]),
                new Compiler(),
                $file,
                file_get_contents(dirname($file).'/../output/'.basename($file))
            ];
        }

        return $data;
    }

    public function minifiedProvider(): array
    {

        $data = [];

        foreach (glob('css/*.css') as $file) {

            $data[] = [

                (new Parser())->setOptions(['flatten_import' => true]),
                (new Compiler())->setOptions(['compress' => true, 'rgba_hex' => true]),
                $file,
                file_get_contents(dirname($file).'/../output/'.str_replace('.css', '.min.css', basename($file)))
            ];
        }

        return $data;
    }
}

