<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Element;
use TBela\CSS\Element\AtRule;
use TBela\CSS\Element\Stylesheet;
use TBela\CSS\Compiler;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

// because git changes \n to \r\n at some point, this causes test failure
function get_content($file) {

    return file_get_contents($file);
}

final class Minify extends TestCase
{
    /**
     * @param Parser $parser
     * @param Compiler $compiler
     * @param string $file
     * @param string $expected
     * @throws Exception
     * @dataProvider minifiedProvider
     */
    public function testMinify(Parser $parser, Compiler $compiler, $file, $expected): void
    {

        $compiler->setData($parser->load($file)->parse());

        $this->assertEquals(
            $expected,
            $compiler->compile()
        );
    }

    public function minifiedProvider(): array
    {

        $data = [];

        foreach (glob('css/*.css') as $file) {

            $parser =  (new Parser())->setOptions(['flatten_import' => true, 'flatten_import' => true]);

            if (basename($file) == 'color.css') {

                $parser->setOptions([
                    'allow_duplicate_declarations' => ['color']
                ]);
            }

            else {

                $parser->setOptions(['allow_duplicate_declarations' => true]);
            }

            $data[] = [

               $parser,
                (new Compiler())->setOptions(['compress' => true, 'convert_color' => true, 'css_level' => true]),
                $file,
                get_content(dirname(dirname($file)).'/output/'.str_replace('.css', '.min.css', basename($file)))
            ];
        }

        return $data;
    }
}

