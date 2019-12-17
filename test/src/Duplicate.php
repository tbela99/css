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
     * @dataProvider removeDuplicateProvider
     */
    public function testRemoveDuplicate(Parser $parser, Compiler $compiler, $file, $expected): void
    {

        $compiler->setData($parser->load($file)->parse());

        $this->assertEquals(
           $compiler->compile(),
            $expected
        );
    }

    public function removeDuplicateProvider(): array
    {

        $data = [];

        foreach ([
            'multiple',
             'multiple1',
             'multiple2'
         ] as $name) {

            foreach (glob(__DIR__.'/../css/'.$name.'.css') as $file) {
         //       foreach (glob(__DIR__.'/../css/*.css') as $file) {

                if (strpos($file, 'multiple') !== false) {

                    $data[] = [

                        new Parser(),
                        new Compiler(),
                        $file,
                        file_get_contents(dirname($file).'/../output/'.basename($file))
                    ];
                }
            }

        }

        return $data;
     //   return [
     //       [new Parser(), new Compiler(['compress' => false, 'rgba_hex' => true]), file_get_contents('./css/multiple2.css'), 'h1{color:#f0f8ff}'],
     //       [new Parser(), new Compiler(['compress' => true, 'rgba_hex' => true]), file_get_contents('./css/multiple2.css'), 'h1{color:#f0f8ff}']
    //    ];
    }
}

