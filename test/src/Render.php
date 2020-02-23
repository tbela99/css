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

    return str_replace("\r\n", "\n", file_get_contents($file));
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
           $compiler->compile(),
            $expected
        );
    }

    /**
     * @param Parser $parser
     * @param Compiler $compiler
     * @param string $file
     * @param string $expected
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
            (string) $stylesheet,
            get_content($expected)
        );
    }

    /*
     * @param Element $data
     * @param Renderer $renderer
     * @param string $expected
     * @dataProvider extractDeclarationsProvider
     * @throws Exception
     */
/*
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
*/
    public function testBuildCss() {

        $step = 0;
        $stylesheet = new Stylesheet();

        $rule = $stylesheet->addRule('div');

        $rule->addDeclaration('background-color', 'white');
        $rule->addDeclaration('color', 'black');

        $this->assertEquals(
            (string) $stylesheet,
           get_content(__DIR__.'/../output/build_css_'.(++$step).'.css')
        );

        $media = $stylesheet->addAtRule('media', 'print');
        $media->append($rule);

        $this->assertEquals(
            (string) $stylesheet,
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css')
        );

        $rule = $stylesheet->addRule('div');

        $rule->addDeclaration('max-width', '100%');
        $rule->addDeclaration('border-width', '0px');

        $this->assertEquals(
            (string) $stylesheet,
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css')
        );

        $media->append($rule);

        $this->assertEquals(
            (string) $stylesheet,
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css')
        );

        $stylesheet->insert($rule, 0);

        $this->assertEquals(
            (string) $stylesheet,
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css')
        );

        $rule->addSelector('.name, .general');

        $this->assertEquals(
            (string) $stylesheet,
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css')
        );

        $rule->removeSelector('div, .general');

        $this->assertEquals(
            (string) $stylesheet,
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css')
        );

        $rule['selector'] = 'a,b,strong';

        $this->assertEquals(
            (string) $stylesheet,
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css')
        );

        $media['value'] = 'all';

        $rule2 = $media->addRule('.new');
        $rule2->addDeclaration('color', 'green');

        $namespace = $stylesheet->addAtRule('namespace', 'svg https://google.com/', 2);

        $this->assertEquals(
            (string) $stylesheet,
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css')
        );

        $stylesheet->insert($rule2, 1);

        $this->assertEquals(
            (string) $stylesheet,
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css')
        );

        $rule->merge($rule2);

        $this->assertEquals(
            (string) $stylesheet,
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css')
        );

        $rule2['parent']->remove($rule2);

        $this->assertEquals(
            (string) $stylesheet,
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css')
        );

        $this->assertEquals(
            (string) $rule2,
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css')
        );

        $this->assertEquals(
            (string) $rule,
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css')
        );

        $rule3 = $media->addRule('ul');

        $rule3->addDeclaration('margin', '0px');
        $rule3->addDeclaration('padding', '5px 3px');

        foreach ($media as $key => $child) {

            $this->assertEquals(
                (string) $child,
                get_content(__DIR__.'/../output/build_css_'.(++$step).'.css')
            );
        }

        $stylesheet->insert($namespace, 0);

        $this->assertEquals(
            (string) $stylesheet,
            get_content(__DIR__.'/../output/build_css_'.(++$step).'.css')
        );
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
                (new Compiler())->setOptions(['compress' => false, 'rgba_hex' => false]),
                $file,
                get_content(dirname(dirname($file)).'/output/'.basename($file))
            ];
        }

        return $data;
    }

    public function minifiedProvider(): array
    {

        $data = [];

        foreach (glob('css/*.css') as $file) {

            $parser =  (new Parser())->setOptions(['flatten_import' => true]);

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
                (new Compiler())->setOptions(['compress' => true, 'rgba_hex' => true]),
                $file,
                get_content(dirname(dirname($file)).'/output/'.str_replace('.css', '.min.css', basename($file)))
            ];
        }

        return $data;
    }

    /**
     * @return array
     * @throws Exception
     */
    /*
    public function extractDeclarationsProvider () {

        $parser = new Parser(dirname(__DIR__).'/css/manipulate.css', [
            'silent' => false,
            'flatten_import' => true
        ]);

        return [
            [$parser->parse(), new Renderer(), __DIR__ .'/../output/extract_font_face_src.css']
        ];
    }
    */
/*
*/
    public function extractAtRuleProvider () {

        $parser = new Parser(__DIR__.'/../css/manipulate.css', [
            'silent' => false,
            'flatten_import' => true
        ]);

        return [
            [$parser->parse(), new Renderer(), __DIR__.'/../output/extract_font_face.css']
        ];
    }
}

