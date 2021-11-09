<?php

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


final class Duplicate extends TestCase
{

    /**
     * @param $content
     * @param string $expected
     * @dataProvider beautifyProvider
     */
    public function testBeautifyDuplicate($content, $expected)
    {

        $this->assertEquals(
            get_content($expected),
            $content
        );
    }

    /**
     * @param $content
     * @param string $expected
     * @dataProvider duplicateRules
     */
    public function testDuplicateRules($content, $expected)
    {

        $this->assertEquals(
            $expected,
            $content
        );
    }

    public function beautifyProvider()
    {

        $parser = new Parser();
        $compiler = new Compiler();

        $data = [];

        $file = './css/color.css';
        $parser->setOptions(['allow_duplicate_declarations' => false]);
        $compiler->setOptions(['allow_duplicate_declarations' => true, 'convert_color' => 'hex']);

            $data[] = [

                $compiler->setData($parser->load($file)->parse())->compile(),
                dirname(dirname($file)). '/output/color.duplicate.css'
            ];

        return $data;
    }

    public function duplicateRules()
    {

        $parser = new Parser();

        $parser->setOptions(['allow_duplicate_rules' => true, 'convert_color' => 'hex']);

        $parser->setContent('
h1 {
  color: green;
  color: blue;
  color: black;
}

h1 {
  color: #000;
  color: aliceblue;
}');

        $data[] = [

            (string) $parser->parse(),
            'h1 {
 color: #000
}
h1 {
 color: #f0f8ff
}'
        ];

        $parser->setContent('
h1 {
  color: green;
  color: blue;
  color: black;
}

h1 {
  color: aliceblue;
  color: #000;
}');

        $data[] = [

            (string) $parser->parse(),
            'h1 {
 color: #000
}'
        ];
        return $data;
    }
}

