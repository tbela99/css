<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Element;
use TBela\CSS\Compiler;
use TBela\CSS\Parser;
use TBela\CSS\Property\PropertyList;

final class Font extends TestCase
{
    /**
     * @param string $css
     * @param string $expected
     * @dataProvider fontWeightProvider
     */
    public function testFontWeight($css, $expected): void
    {

        $this->assertEquals(
            $expected,
            $css
        );
    }

    /**
     * @param string $css
     * @param string $expected
     * @dataProvider fontComputeProvider
     */
    public function testComputeFont($css, $expected): void
    {

        $this->assertEquals(
            $expected,
            $css
        );
    }

    /**
     * @param string $css
     * @param string $expected
     * @dataProvider fontSrcProvider
     */
    public function testfontSrcProvider($css, $expected): void
    {

        $this->assertEquals(
            $expected,
            $css
        );
    }

    public function fontSrcProvider () {

        $data = [];

        $parser = new Parser("@font-face{font-family:'CenturyGothic';src:url('/CenturyGothic.woff') format('woff');font-weight:400;}");

        $data[] = [(string) $parser->parse(), '@font-face {
 font-family: CenturyGothic;
 font-weight: 400;
 src: url(/CenturyGothic.woff) format("woff")
}'];

        return $data;
    }

    public function fontComputeProvider () {

        $data = [];

        $propertyList = new PropertyList();

        $propertyList->set('font', 'italic 1.2em "Fira Sans", serif');

        $data[] = [(string) $propertyList, 'font: italic 1.2em "Fira Sans", serif'];

        $propertyList->set('line-height', 2);
        $data[] = [(string) $propertyList, 'font: italic 1.2em/2 "Fira Sans", serif'];

        $propertyList->set('font-weight', 'bold');
        $data[] = [(string) $propertyList, 'font: bold italic 1.2em/2 "Fira Sans", serif'];

        $propertyList->set('font-size', '16px');
        $data[] = [(string) $propertyList, 'font: bold italic 16px/2 "Fira Sans", serif'];

        $propertyList->set('font-variant', 'small-caps');
        $data[] = [(string) $propertyList, 'font: bold italic small-caps 16px/2 "Fira Sans", serif'];

        $propertyList->set('font-weight', '400');
        $data[] = [(string) $propertyList, 'font: italic small-caps 16px/2 "Fira Sans", serif'];

        $propertyList->set('font', '400 var(--default-font-size) \'Trebuchet MS\', sans-serif');
        $data[] = [(string) $propertyList, 'font: 400 var(--default-font-size) \'Trebuchet MS\', sans-serif'];

        $propertyList->set('font-size', '16px');
        $data[] = [(string) $propertyList, "font: 400 var(--default-font-size) 'Trebuchet MS', sans-serif;\nfont-size: 16px"];

        $propertyList->set('font', '11px \'Trebuchet MS\', sans-serif');
        $data[] = [(string) $propertyList, "font: 11px 'Trebuchet MS', sans-serif"];

        $propertyList->set('font-size', '16px');
        $data[] = [(string) $propertyList, "font: 16px 'Trebuchet MS', sans-serif"];

        $propertyList->set('font-weight', 'bold');
        $data[] = [(string) $propertyList, "font: bold 16px 'Trebuchet MS', sans-serif"];

        return $data;
    }

    public function fontProvider () {

        $data = [];

        $compiler = new Compiler(['compress' => true]);

        $data[] = [$compiler->setContent('
strong {

    font-family: "Arial", "Arial Black", "Bitstream Vera Serif Bold", serif;
}
')->compile(), 'strong{font-family:Arial,"Arial Black","Bitstream Vera Serif Bold",serif}'];

        $data[] = [$compiler->setContent('
strong {

    font: bold 14px "Bitstream Vera Serif Bold",serif;
}
')->compile(), 'strong{font:700 14px "Bitstream Vera Serif Bold",serif}'];

        $data[] = [$compiler->setContent('
strong {

    font: light 14px "Bitstream Vera Serif Bold",serif;
}
')->compile(), 'strong{font:300 14px "Bitstream Vera Serif Bold",serif}'];

        $data[] = [$compiler->setContent('
strong {

    font: "ultra bold" 14px "Bitstream Vera Serif Bold",serif;
}
')->compile(), 'strong{font:800 14px "Bitstream Vera Serif Bold",serif}'];

        $data[] = [$compiler->setContent('
strong {

    font: Regular 14px "Bitstream Vera Serif Bold",serif;
}
')->compile(), 'strong{font:14px "Bitstream Vera Serif Bold",serif}'];

        return $data;
    }

    public function fontWeightProvider () {

        $data = [];

        $compiler = new Compiler(['compress' => true]);

        $data[] = [$compiler->setContent('
strong {

font-weight: Extra Black;
}
')->compile(), 'strong{font-weight:950}'];

        $data[] = [$compiler->setContent('
strong {

font-weight: light;
}
')->compile(), 'strong{font-weight:300}'];

        $data[] = [$compiler->setContent('
strong {

font-weight: ultra bold;
}
')->compile(), 'strong{font-weight:800}'];

        $data[] = [$compiler->setContent('
strong {

font-weight: Regular;
}
')->compile(), 'strong{font-weight:400}'];

        return $data;
    }
}

