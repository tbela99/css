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


final class Render extends TestCase
{
    /**
     * @param Parser $parser
     * @param Compiler $compiler
     * @param string $file
     * @param string $expected
     * @throws Exception
     * @dataProvider beautifyProvider
     */
    public function testBeautifyDuplicate(Parser $parser, Compiler $compiler, $file, $expected): void
    {

        $compiler->setData($parser->load($file)->parse());

        $this->assertEquals(
            get_content($expected),
           $compiler->compile()
        );
    }

    /**
     * @param Element $data
     * @param Renderer $renderer
     * @param string $expected
     * @dataProvider extractAtRuleProvider
     * @throws Exception
     */
    public function testExtractAtRule(Element $data, Renderer $renderer, $expected)
    {

        $fontFace = [];
        $stack = [$data];

        while ($current = array_shift($stack)) {

            foreach ($current as $node) {

                if ($node instanceof AtRule) {

                    switch ($node->getName()) {

                        case 'font-face':

                            $fontFace[] = $node->copy();

                            break;

                        case 'media':

                            $stack[] = $node;
                            break;

                    }
                }
            }
        }

        // deduplicate ast
        $stylesheet = new Stylesheet();

        foreach ($fontFace as $font) {

            $stylesheet->append($font);
        }

        $ast = (new Parser())->deduplicate($stylesheet);

        $stylesheet = Element::getInstance($ast);

        $this->assertEquals(
            get_content($expected),
            (string) $stylesheet
        );
    }

    /**
     * @param Element $data
     * @param Renderer $renderer
     * @param string $expected
     * @dataProvider extractDeclarationsProvider
     * @throws Exception
     */

    public function testExtractDeclarations(Element $data, Renderer $renderer, $expected)
    {

        $fontFaceSrc = [];
        $stack = [$data];

        while ($current = array_shift($stack)) {

            foreach ($current as $node) {

                if ($node instanceof AtRule) {

                    switch ($node->getName()) {

                        case 'font-face':

                            foreach ($node as $declaration) {

                                if ($declaration->getName() == 'src') {

                                    $fontFaceSrc[] = $declaration->copy();
                                }
                            }

                            break;

                        case 'media':

                            $stack[] = $node;
                            break;

                    }
                }
            }
        }

        // deduplicate ast
        $stylesheet = new Stylesheet();

        foreach ($fontFaceSrc as $font) {

            $stylesheet->append($font);
        }

        $ast = (new Parser())->deduplicate($stylesheet);

        $stylesheet = Element::getInstance($ast);

        $this->assertEquals(
            (string) $stylesheet,
            get_content($expected)
        );
    }

    /**
     * @throws Exception
     * @dataProvider BuildCssProvider
     */
    public function testBuildCss($expected, $content) {

        $this->assertEquals(
           $expected,
           $content
        );
    }

    public function BuildCssProvider() {

        $data = [];
        $step = 0;
        $stylesheet = new Stylesheet();

        $rule = $stylesheet->addRule('div');

        $rule->addDeclaration('background-color', 'white');
        $rule->addDeclaration('color', 'black');

        $data[] = [
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css'),
            (string) $stylesheet
        ];

        $media = $stylesheet->addAtRule('media', 'print');
        $media->append($rule);

        $data[] = [
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css'),
            (string) $stylesheet
        ];

        $rule = $stylesheet->addRule('div');

        $rule->addDeclaration('max-width', '100%');
        $rule->addDeclaration('border-width', '0px');

        $data[] = [
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css'),
            (string) $stylesheet
        ];

        $media->append($rule);

        $data[] = [
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css'),
            (string) $stylesheet
        ];

        $stylesheet->insert($rule, 0);

        $data[] = [
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css'),
            (string) $stylesheet
        ];

        $rule->addSelector('.name, .general');

        $data[] = [
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css'),
            (string) $stylesheet
        ];

        $rule->removeSelector('div, .general');

        $data[] = [
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css'),
            (string) $stylesheet
        ];

        $rule['selector'] = 'a,b,strong';

        $data[] = [
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css'),
            (string) $stylesheet
        ];

        $media['value'] = 'all';

        $rule2 = $media->addRule('.new');
        $rule2->addDeclaration('color', 'green');

        $namespace = $stylesheet->addAtRule('namespace', 'svg url(https://google.com/)', 2);

        $data[] = [
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css'),
            (string) $stylesheet
        ];

        $stylesheet->insert($rule2, 1);

        $data[] = [
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css'),
            (string) $stylesheet
        ];

        $rule->merge($rule2);

        $data[] = [
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css'),
            (string) $stylesheet
        ];

        $rule2['parent']->remove($rule2);

        $data[] = [
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css'),
            (string) $stylesheet
        ];

        $data[] = [
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css'),
            (string) $rule2
        ];

        $data[] = [
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css'),
            (string) $rule
        ];

        $rule3 = $media->addRule('ul');

        $rule3->addDeclaration('margin', '0px');
        $rule3->addDeclaration('padding', '5px 3px');

        foreach ($media as $key => $child) {

            $data[] = [
                get_content(__DIR__.'/../output/build_css_'.(++$step).'.css'),
                (string) $child
            ];
        }

        $stylesheet->insert($namespace, 0);


        $data[] = [
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css'),
            (string) $stylesheet
        ];

        return $data;
    }

    public function beautifyProvider(): array
    {

        $data = [];

        foreach (glob('css/*.css') as $file) {

            $parser = (new Parser())->setOptions(['flatten_import' => true]);

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
                new Compiler(['compress' => false, 'convert_color' => true]),
                $file,
                dirname(dirname($file)).'/output/'.basename($file)
            ];
        }

        return $data;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function extractDeclarationsProvider () {

        $parser = new Parser('', [
            'silent' => false,
            'flatten_import' => true
        ]);

        return [
            [$parser->load(dirname(__DIR__).'/css/manipulate.css')->parse(), new Renderer(), __DIR__ .'/../output/extract_font_face_src.css']
        ];
    }
/*
*/
    public function extractAtRuleProvider () {

        $parser = new Parser('', [
            'silent' => false,
            'flatten_import' => true
        ]);

        return [
            [$parser->load(__DIR__.'/../css/manipulate.css')->parse(), new Renderer(), __DIR__.'/../output/extract_font_face.css']
        ];
    }
}

