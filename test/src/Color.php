<?php
// declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Compiler;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

final class Color extends TestCase
{
    /**
     * @param Parser $parser
     * @param string $expected
     *
     * @dataProvider cssVariableProvider
     */
    public function testCssVariable (Parser $parser, $expected) {

        $this->assertEquals(
            $expected,
            (string) $parser
        );
    }
    /**
     * @param Compiler $compiler
     * @param $content
     * @param string $expected
     * @throws Exception
     * @dataProvider rgbaCss4ColorProvider
     */
    public function testRgbaCss4Color(Compiler $compiler, $content, $expected)
    {
        $this->assertEquals(
            $expected,
           $compiler->setContent($content)->compile()
        );
    }

    /**
     * @param Compiler $compiler
     * @param $content
     * @param string $expected
     * @throws Exception
     * @dataProvider rgbaColorProvider
     */
    public function testRgbaColor(Compiler $compiler, $content, $expected)
    {
        $this->assertEquals(
            $expected,
            $compiler->setContent($content)->compile()
        );
    }

    /**
     * @param Compiler $compiler
     * @param $content
     * @param string $expected
     * @throws Exception
     * @dataProvider rgbaColorCompressProvider
     */
    public function testRgbaColorCompress(Compiler $compiler, $content, $expected)
    {
        $this->assertEquals(
            $expected,
            $compiler->setContent($content)->compile()
        );
    }
    /**
     * @param Compiler $compiler
     * @param $content
     * @param string $expected
     * @throws Exception
     * @dataProvider rgbaHexColorProvider
     */
    public function testRgbaHexColor(Compiler $compiler, $content, $expected)
    {
        $this->assertEquals(
            $expected,
            $compiler->setContent($content)->compile()
        );
    }

    /**
     * @param Compiler $compiler
     * @param $content
     * @param string $expected
     * @throws Exception
     * @dataProvider rgbaHexColorCompressProvider
     */
    public function testRgbaHexColorCompress(Compiler $compiler, $content, $expected)
    {
        $this->assertEquals(
            $expected,
            $compiler->setContent($content)->compile()
        );
    }

    /**
     * @param Compiler $compiler
     * @param $content
     * @param string $expected
     * @throws Exception
     * @dataProvider blackColorProvider
     */
    public function testBlackColor(Compiler $compiler, $content, $expected)
    {
        $this->assertEquals(
            $expected,
            $compiler->setContent($content)->compile()
        );
    }

    /**
     * @param Compiler $compiler
     * @param $content
     * @param string $expected
     * @throws Exception
     * @dataProvider redColorProvider
     */
    public function testRedColor(Compiler $compiler, $content, $expected)
    {
        $this->assertEquals(
            $expected,
            $compiler->setContent($content)->compile()
        );
    }

    /**
     * @param Compiler $compiler
     * @param $content
     * @param string $expected
     * @throws Exception
     * @dataProvider yellowColorProvider
     */
    public function testYellowColor(Compiler $compiler, $content, $expected)
    {
        $this->assertEquals(
            $expected,
            $compiler->setContent($content)->compile()
        );
    }

    /**
     * @param Parser $parser
     * @param $content
     * @param string $expected
     * @throws Exception
     * @dataProvider yellowColorAstProvider
     */
    public function testYellowColorAst(Parser $parser, Renderer $renderer, $content, $expected)
    {
        $this->assertEquals(
            $expected,
            $renderer->renderAst($parser->setContent($content)->getAst())
        );
    }

    public function rgbaColorProvider () {

        $compiler = new Compiler(['css_level' => 3, 'convert_color' => true]);

        $data = [];

        $data[] = [$compiler,
            'p {
	/* Functional syntax with floats value */
	color: rgba(255, 0, 153.6, 1);
}', 'p {
 /* Functional syntax with floats value */
 color: #f09
}'];

        $data[] = [$compiler,
            'p {
	/* Functional syntax with floats value */
	color: rgba(1e2, .5e1, .5e0, +.25e2%);
}', 'p {
 /* Functional syntax with floats value */
 color: rgba(100, 5, .5, .25)
}'];

        $data[] = [$compiler,
            '
p {
	/* red 50% translucent #ff000080 */
	color: #ff000080;
}', 'p {
 /* red 50% translucent #ff000080 */
 color: rgba(255, 0, 0, .5)
}'];

        $data[] = [$compiler,
            'p {
	/* red 50% translucent rgba rgba(255, 0, 0, .5) */
	color: rgba(255, 0, 0, .5);
}', 'p {
 /* red 50% translucent rgba rgba(255, 0, 0, .5) */
 color: rgba(255, 0, 0, .5)
}'];

        $data[] = [$compiler,
            'p {
	/* red 50% translucent hsla hsl(0, 100%, 50%, .5) */
	color: hsl(0, 100%, 50%, .5);
}', 'p {
 /* red 50% translucent hsla hsl(0, 100%, 50%, .5) */
 color: hsla(0, 100%, 50%, .5)
}'];

        $data[] = [$compiler,
            'p {

	/* red 50% translucent hsla(0, 100%, 50%, .5) */
	color: hsla(0, 100%, 50%, .5);
}', 'p {
 /* red 50% translucent hsla(0, 100%, 50%, .5) */
 color: hsla(0, 100%, 50%, .5)
}'];

        return $data;
    }

    public function cssVariableProvider () {

        $data = [];

        $data[] = [new TBela\CSS\Parser('.a{color:rgba(var(--cassiopeia-color-primary), .25);
background-color: rgba(255, 255, 255, 1);
}'),
            '.a {
 color: rgba(var(--cassiopeia-color-primary), .25);
 background-color: white
}'];

        return $data;
    }

    public function rgbaCss4ColorProvider () {

        $compiler = new Compiler(['compress' => false, 'convert_color' => false, 'css_level' => 4]);

        $data = [];

        $data[] = [$compiler,
            'p {
	/* Functional syntax with floats value */
	color: rgba(255, 0, 153.6, 1);
}', 'p {
 /* Functional syntax with floats value */
 color: rgb(255 0 153.6)
}'];

        $data[] = [$compiler,
            'p {
	/* Functional syntax with floats value */
	color: rgba(1e2, .5e1, .5e0, +.25e2%);
}', 'p {
 /* Functional syntax with floats value */
 color: rgb(100 5 .5 / .25)
}'];

        $data[] = [$compiler,
            '
p {
	/* red 50% translucent #ff000080 */
	color: #ff000080;
}', 'p {
 /* red 50% translucent #ff000080 */
 color: #ff000080
}'];

        $data[] = [$compiler,
            'p {
	/* red 50% translucent rgba rgba(255, 0, 0, .5) */
	color: rgba(255, 0, 0, .5);
}', 'p {
 /* red 50% translucent rgba rgba(255, 0, 0, .5) */
 color: rgb(255 0 0 / .5)
}'];

        $data[] = [$compiler,
            'p {
	/* red 50% translucent hsla hsl(0, 100%, 50%, .5) */
	color: hsl(0, 100%, 50%, .5);
}', 'p {
 /* red 50% translucent hsla hsl(0, 100%, 50%, .5) */
 color: hsl(0 100% 50% / .5)
}'];

        $data[] = [$compiler,
            'p {

	/* red 50% translucent hsla(0, 100%, 50%, .5) */
	color: hsla(0, 100%, 50%, .5);
}', 'p {
 /* red 50% translucent hsla(0, 100%, 50%, .5) */
 color: hsl(0 100% 50% / .5)
}'];

        return $data;
    }

    public function rgbaColorCompressProvider () {

        $compiler = new Compiler(['compress' => true, 'convert_color' => true, 'css_level' => 3]);

        $data = [];

        $data[] = [$compiler,
            'p {
	/* Functional syntax with floats value */
	color: rgba(255, 0, 153.6, 1);
}', 'p{color:#f09}'];

        $data[] = [$compiler,
            'p {
	/* Functional syntax with floats value */
	color: rgba(1e2, .5e1, .5e0, +.25e2%);
}', 'p{color:rgba(100,5,.5,.25)}'];

        $data[] = [$compiler,
            '
p {
	/* red 50% translucent #ff000080 */
	color: #ff000080;
}', 'p{color:rgba(255,0,0,.5)}'];

        $data[] = [$compiler,
            'p {
	/* red 50% translucent rgba rgba(255, 0, 0, .5) */
	color: rgba(255, 0, 0, .5);
}', 'p{color:rgba(255,0,0,.5)}'];

        $data[] = [$compiler,
            'p {
	/* red 50% translucent hsla hsl(0, 100%, 50%, .5) */
	color: hsl(0, 100%, 50%, .5);
}', 'p{color:hsla(0,100%,50%,.5)}'];

        $data[] = [$compiler,
            'p {

	/* red 50% translucent hsla(0, 100%, 50%, .5) */
	color: hsla(0, 100%, 50%, .5);
}', 'p{color:hsla(0,100%,50%,.5)}'];

        return $data;
    }

    public function rgbaHexColorProvider () {

        $compiler = new Compiler(['convert_color' => true, 'css_level' => 4]);

        $data = [];

        $data[] = [$compiler,
            'p {
	/* Functional syntax with floats value */
	color: rgba(255, 0, 153.6, 1);
}', 'p {
 /* Functional syntax with floats value */
 color: #f09
}'];

        $data[] = [$compiler,
            'p {
	/* Functional syntax with floats value */
	color: rgba(1e2, .5e1, .5e0, +.25e2%);
}', 'p {
 /* Functional syntax with floats value */
 color: #64050040
}'];

        $data[] = [$compiler,
            '
p {
	/* red 50% translucent #ff000080 */
	color: #ff000080;
}', 'p {
 /* red 50% translucent #ff000080 */
 color: #ff000080
}'];

        $data[] = [$compiler,
            'p {
	/* red 50% translucent rgba rgba(255, 0, 0, .5) */
	color: rgba(255, 0, 0, .5);
}', 'p {
 /* red 50% translucent rgba rgba(255, 0, 0, .5) */
 color: #ff000080
}'];

        $data[] = [$compiler,
            'p {
	/* red 50% translucent hsla hsl(0, 100%, 50%, .5) */
	color: hsl(0, 100%, 50%, .5);
}', 'p {
 /* red 50% translucent hsla hsl(0, 100%, 50%, .5) */
 color: #ff000080
}'];

        $data[] = [$compiler,
            'p {

	/* red 50% translucent hsla(0, 100%, 50%, .5) */
	color: hsla(0, 100%, 50%, .5);
}', 'p {
 /* red 50% translucent hsla(0, 100%, 50%, .5) */
 color: #ff000080
}'];

        return $data;
    }

    public function rgbaHexColorCompressProvider () {

        $compiler = new Compiler(['compress' => true, 'convert_color' => true, 'css_level' => 4]);

        $data = [];

        $data[] = [$compiler,
            'p {
	/* Functional syntax with floats value */
	color: rgba(255, 0, 153.6, 1);
}', 'p{color:#f09}'];

        $data[] = [$compiler,
            'p {
	/* Functional syntax with floats value */
	color: rgba(1e2, .5e1, .5e0, +.25e2%);
}', 'p{color:#64050040}'];

        $data[] = [$compiler,
            '
p {
	/* red 50% translucent #ff000080 */
	color: #ff000080;
}', 'p{color:#ff000080}'];

        $data[] = [$compiler,
            'p {
	/* red 50% translucent rgba rgba(255, 0, 0, .5) */
	color: rgba(255, 0, 0, .5);
}', 'p{color:#ff000080}'];

        $data[] = [$compiler,
            'p {
	/* red 50% translucent hsla hsl(0, 100%, 50%, .5) */
	color: hsl(0, 100%, 50%, .5);
}', 'p{color:#ff000080}'];

        $data[] = [$compiler,
            'p {

	/* red 50% translucent hsla(0, 100%, 50%, .5) */
	color: hsla(0, 100%, 50%, .5);
}', 'p{color:#ff000080}'];

        return $data;
    }

    public function blackColorProvider () {

        $compiler = new Compiler(['convert_color' => 'hex']);

        $data = [];

        $data[] = [$compiler,
            '
p {
	/* color black black */
	color: black;
}', 'p {
 /* color black black */
 color: #000
}'];

        $data[] = [$compiler,
            '
p {
	/* color black #000 */
	color: #000;
}', 'p {
 /* color black #000 */
 color: #000
}'];

        $data[] = [$compiler,
            '
p {
	/* color black #000000 */
	color: #000000;
}', 'p {
 /* color black #000000 */
 color: #000
}'];

        $data[] = [$compiler,
            '
p {
	/* color black rgb(0, 0, 0) */
	color: rgb(0, 0, 0);
}', 'p {
 /* color black rgb(0, 0, 0) */
 color: #000
}'];

        $data[] = [$compiler,
            '
p {
	/* color black rgba(0, 0, 0, 1) */
	color: rgba(0, 0, 0, 1);
}', 'p {
 /* color black rgba(0, 0, 0, 1) */
 color: #000
}'];

        $data[] = [$compiler,'
p {
/* color black #000000ff */
	color: #000;
}', 'p {
 /* color black #000000ff */
 color: #000
}'];

        return $data;
    }

    public function redColorProvider () {

        $compiler = new Compiler();

        $data = [];

        $data[] = [$compiler,
            'p {
	/* These examples all specify the same color: red. */
	/* color red red */
	color: red;
}', 'p {
 /* These examples all specify the same color: red. */
 /* color red red */
 color: red
}'];

        $data[] = [$compiler,
            'p {
	/* color red #f00 */
	color: #f00;
}', 'p {
 /* color red #f00 */
 color: red
}'];

        $data[] = [$compiler,
            'p {
	/* color red #f00F */
	color: #f00F;
}', 'p {
 /* color red #f00F */
 color: red
}'];

        $data[] = [$compiler,
            'p {
	/* color red #ff0000Ff */
	color: #ff0000Ff;
}', 'p {
 /* color red #ff0000Ff */
 color: red
}'];

        $data[] = [$compiler,
            'p {
	/* color red #ff0000 */
	color: #ff0000;
}', 'p {
 /* color red #ff0000 */
 color: red
}'];

        $data[] = [$compiler,
            'p {
	/* color red rgb(255,0,0) */
	color: rgb(255,0,0);
}', 'p {
 /* color red rgb(255,0,0) */
 color: red
}'];

        $data[] = [$compiler,
            'p {
	/* color red rgba(255,0,0,1); */
	color: rgba(255,0,0,1);
}', 'p {
 /* color red rgba(255,0,0,1); */
 color: red
}'];

        $data[] = [$compiler,'p {
	/* color red rgb(100%, 0%, 0%) */
	color: rgb(100%, 0%, 0%);
}', 'p {
 /* color red rgb(100%, 0%, 0%) */
 color: red
}'];

        $data[] = [$compiler,'p {
	/* color red rgba(100%, 0%, 0%, 1) */
	color: rgba(100%, 0%, 0%, 1);
}', 'p {
 /* color red rgba(100%, 0%, 0%, 1) */
 color: red
}'];

        $data[] = [$compiler,'p {
	/* color red hsl(0, 100%, 50%) */
	color: hsl(0, 100%, 50%);
}', 'p {
 /* color red hsl(0, 100%, 50%) */
 color: red
}'];
        $data[] = [$compiler,'p {
	/* color red hsla(0, 100%, 50%, 1) */
	color: hsla(0, 100%, 50%, 1);
}', 'p {
 /* color red hsla(0, 100%, 50%, 1) */
 color: red
}'];

        return $data;
    }

    public function yellowColorProvider () {

        $compiler = new Compiler(['convert_color' => 'hex']);

        $data = [];

        $data[] = [$compiler,
            'p {
	/* These examples all specify the same color: yellow. */
	/* color #ff0 yellow */
	color: yellow;
}', 'p {
 /* These examples all specify the same color: yellow. */
 /* color #ff0 yellow */
 color: #ff0
}'];

        $data[] = [$compiler,
            'p {
	/* color #ff0 #ffff00 */
	color: #ffff00;
}', 'p {
 /* color #ff0 #ffff00 */
 color: #ff0
}'];

        $data[] = [$compiler,
            'p {
	/* color #ff0 #ff0F */
	color: #ff0F;
}', 'p {
 /* color #ff0 #ff0F */
 color: #ff0
}'];

        $data[] = [$compiler,
            'p {
	/* color #ff0 #ff0000Ff */
	color: #ffff00Ff;
}', 'p {
 /* color #ff0 #ff0000Ff */
 color: #ff0
}'];

        $data[] = [$compiler,
            'p {
	/* color #ff0 rgb(255,255,0) */
	color: rgb(255,255,0);
}', 'p {
 /* color #ff0 rgb(255,255,0) */
 color: #ff0
}'];

        $data[] = [$compiler,
            'p {
	/* color #ff0 rgba(255,255,0,1); */
	color: rgba(255,255,0,1);
}', 'p {
 /* color #ff0 rgba(255,255,0,1); */
 color: #ff0
}'];

        $data[] = [$compiler,'p {
	/* color #ff0 rgb(100%, 100%, 0%) */
	color: rgb(100%, 100%, 0%);
}', 'p {
 /* color #ff0 rgb(100%, 100%, 0%) */
 color: #ff0
}'];

        $data[] = [$compiler,'p {
	/* color #ff0 rgba(100%, 100%, 0%, 1) */
	color: rgba(100%, 100%, 0%, 1);
}', 'p {
 /* color #ff0 rgba(100%, 100%, 0%, 1) */
 color: #ff0
}'];

        $data[] = [$compiler,'p {
	/* color #ff0 hsl(60, 100%, 50%) */
	color: hsl(60, 100%, 50%);
}', 'p {
 /* color #ff0 hsl(60, 100%, 50%) */
 color: #ff0
}'];
        $data[] = [$compiler,'p {
	/* color #ff0 hsla(60, 100%, 50%, 1) */
	color: hsla(60, 100%, 50%, 1);
}', 'p {
 /* color #ff0 hsla(60, 100%, 50%, 1) */
 color: #ff0
}'];

        return $data;
    }

    public function yellowColorAstProvider () {

        $parser = new Parser('', ['convert_color' => 'hex']);
        $renderer = new Renderer(['convert_color' => 'hex']);

        $data = [];

        $data[] = [$parser,
            $renderer,
            'p {
	/* These examples all specify the same color: yellow. */
	/* color #ff0 yellow */
	color: yellow;
}', 'p {
 /* These examples all specify the same color: yellow. */
 /* color #ff0 yellow */
 color: #ff0
}'];

        $data[] = [$parser,
            $renderer,
            'p {
	/* color #ff0 #ffff00 */
	color: #ffff00;
}', 'p {
 /* color #ff0 #ffff00 */
 color: #ff0
}'];

        $data[] = [$parser,
            $renderer,
            'p {
	/* color #ff0 #ff0F */
	color: #ff0F;
}', 'p {
 /* color #ff0 #ff0F */
 color: #ff0
}'];

        $data[] = [$parser,
            $renderer,
            'p {
	/* color #ff0 #ff0000Ff */
	color: #ffff00Ff;
}', 'p {
 /* color #ff0 #ff0000Ff */
 color: #ff0
}'];

        $data[] = [$parser,
            $renderer,
            'p {
	/* color #ff0 rgb(255,255,0) */
	color: rgb(255,255,0);
}', 'p {
 /* color #ff0 rgb(255,255,0) */
 color: #ff0
}'];

        $data[] = [$parser,
            $renderer,
            'p {
	/* color #ff0 rgba(255,255,0,1); */
	color: rgba(255,255,0,1);
}', 'p {
 /* color #ff0 rgba(255,255,0,1); */
 color: #ff0
}'];

        $data[] = [$parser,
            $renderer,
            'p {
	/* color #ff0 rgb(100%, 100%, 0%) */
	color: rgb(100%, 100%, 0%);
}', 'p {
 /* color #ff0 rgb(100%, 100%, 0%) */
 color: #ff0
}'];

        $data[] = [$parser,
            $renderer,
            'p {
	/* color #ff0 rgba(100%, 100%, 0%, 1) */
	color: rgba(100%, 100%, 0%, 1);
}', 'p {
 /* color #ff0 rgba(100%, 100%, 0%, 1) */
 color: #ff0
}'];

        $data[] = [$parser,
            $renderer, 'p {
	/* color #ff0 hsl(60, 100%, 50%) */
	color: hsl(60, 100%, 50%);
}', 'p {
 /* color #ff0 hsl(60, 100%, 50%) */
 color: #ff0
}'];
        $data[] = [$parser,
            $renderer,
            'p {
	/* color #ff0 hsla(60, 100%, 50%, 1) */
	color: hsla(60, 100%, 50%, 1);
}', 'p {
 /* color #ff0 hsla(60, 100%, 50%, 1) */
 color: #ff0
}'];

        return $data;
    }
}

