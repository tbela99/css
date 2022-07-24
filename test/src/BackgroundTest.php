<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;
use TBela\CSS\Property\PropertyList;
use TBela\CSS\Value;

final class BackgroundTest extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider backgroundSizeProvider
     */
    public function testBackgroundSize($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
          $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider backgroundAttachmentProvider
     */
    public function testBackgroundAttachment($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider backgroundRepeatProvider
     */
    public function testBackgroundRepeat($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider backgroundPositionProvider
     */
    public function testBackgroundPosition($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider backgroundImageProvider
     */
    public function testBackgroundImage($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider backgroundProvider
     */
    public function testBackground($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider backgroundOriginProvider
     */
    public function testBackgroundOrigin($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider backgroundClipProvider
     */
    public function testBackgroundClip($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider backgroundComputeProvider
     */
    public function testBackgroundCompute($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    public function backgroundSizeProvider() {

        $data = [];

        $data[] = ['.s1 {
 background-size: cover
}
.s2 {
 background-size: auto 10%, auto 10%, auto, 10%
}', (string) new Parser('
.s1 {

    background-size: cover;
    }
    
.s2 {


    background-size: auto 10%, auto 10%, auto auto, 10% auto;
    }
')];
        return $data;
    }

    public function backgroundAttachmentProvider() {

        $data = [];

        $data[] = ['.s1 {
 background-attachment: scroll
}
.s2 {
 background-attachment: fixed, local, scroll, fixed
}', (string) new Parser('.s1 {
    background-attachment: scroll
}
.s2 {

    background-attachment: fixed, local, scroll, 
    fixed
}
')];
        return $data;
    }

    public function backgroundRepeatProvider() {

        $data = [];

        $data[] = ['.s1 {
 background-repeat: repeat
}
.s2 {
 background-repeat: no-repeat space, round, round repeat, repeat round
}', (string) new Parser('

.s1 {

    background-repeat: repeat repeat;
    }
    
.s2 {

    background-repeat: no-repeat space, round   round , round repeat, repeat round
    }
')];
        return $data;
    }

    public function backgroundPositionProvider() {

        $data = [];

        // invalid
        $data[] = [
            '[{"type":"background-position","value":"left"}]',
            json_encode(Value::parse('left ', 'background-position', true, '', '', true))];

        $data[] = [
            '[{"value":"left","type":"background-position"},{"type":"whitespace"},{"value":"center","type":"background-position"}]',
            json_encode(Value::parse('left  center', 'background-position', true, '', '', true))];

        $data[] = [
            '[{"value":"right","type":"background-position"},{"type":"whitespace"},{"value":"center","type":"background-position"},{"type":"whitespace"},{"value":"10","type":"background-position","unit":"%"}]',
            json_encode(Value::parse(' right center 10%  ', 'background-position', true, '', '', true))];

        $data[] = [
            '[{"value":"right","type":"background-position"},{"type":"whitespace"},{"value":"0","type":"background-position"},{"type":"whitespace"},{"value":"bottom","type":"background-position"},{"type":"whitespace"},{"value":"50","type":"background-position","unit":"%"}]',
            json_encode(Value::parse(' right 0 bottom 50%  ', 'background-position', true, '', '', true))];

        return $data;
    }

    public function backgroundImageProvider() {

        $data = [];

        $data[] = [
            '[{"type":"background-image","value":"none"}]',
            json_encode(Value::parse('none', 'background-image' , true, '', '', true))];

        $data[] = [
            '[{"name":"url","type":"background-image","arguments":[{"type":"css-string","value":"cat.jpg"}]}]',
            json_encode(Value::parse('url(cat.jpg)', 'background-image' , true, '', '', true))];

        return $data;
    }

    public function backgroundOriginProvider() {

        $data = [];

        $data[] = [
            '[{"type":"background-origin","value":"padding-box"}]',
            json_encode(Value::parse('padding-box', 'background-origin' , true, '', '', true))];

        $data[] = [
            '[{"type":"background-origin","value":"border-box"}]',
            json_encode(Value::parse('border-box', 'background-origin' , true, '', '', true))];

        $data[] = [
            '[{"type":"background-origin","value":"content-box"}]',
            json_encode(Value::parse('content-box', 'background-origin' , true, '', '', true))];

        return $data;
    }

    public function backgroundClipProvider() {

        $data = [];

        $data[] = [
            '[{"type":"background-clip","value":"padding-box"}]',
            json_encode(Value::parse('padding-box', 'background-clip' , true, '', '', true))];

        $data[] = [
            '[{"type":"background-clip","value":"border-box"}]',
            json_encode(Value::parse('border-box', 'background-clip' , true, '', '', true))];

        $data[] = [
            '[{"type":"background-clip","value":"content-box"}]',
            json_encode(Value::parse('content-box', 'background-clip' , true, '', '', true))];

        $data[] = [
            '[{"type":"background-clip","value":"text"}]',
            json_encode(Value::parse('text', 'background-clip' , true, '', '', true))];

        return $data;
    }

    public function backgroundProvider() {

        $data = [];

        $data[] = [
            '[{"type":"background-repeat","value":"round"}]',
            json_encode(Value::parse('round   round ', 'background' , true, '', '', true))];

        $data[] = [
            '[{"name":"url","type":"background-image","arguments":[{"type":"css-string","value":"images\/hero.jpg"}]},{"type":"whitespace"},{"type":"background-repeat","value":"round"}]',
            json_encode(Value::parse('url(images/hero.jpg) round   round ', 'background' , true, '', '', true))];

        $data[] = [
            '[{"value":"none","type":"background"}]',
            json_encode(Value::parse('none ', 'background' , true, '', '', true))];

        $data[] = [
            '[{"value":"no-repeat","type":"background-repeat"},{"type":"whitespace"},{"name":"url","type":"background-image","arguments":[{"type":"css-string","value":"sourcemap\/images\/bg.png"}]},{"type":"whitespace"},{"value":"50","type":"background-position","unit":"%"},{"type":"whitespace"},{"value":"50","type":"background-position","unit":"%"},{"type":"separator","value":"\/"},{"type":"background-size","value":"cover"}]',
            json_encode(Value::parse('no-repeat url(sourcemap/images/bg.png) 50% 50%/cover ', 'background' , true, '', '', true))];

        $data[] = [
            '[{"value":"center","type":"background-position"},{"type":"separator","value":"\/"},{"value":"contain","type":"background-size"},{"type":"whitespace"},{"value":"no-repeat","type":"background-repeat"},{"type":"whitespace"},{"name":"url","type":"background-image","arguments":[{"type":"css-string","value":"\"..\/..\/media\/examples\/firefox-logo.svg\""}]},{"type":"separator","value":","},{"value":"#eee","type":"background-color","colorType":"hex"},{"type":"whitespace"},{"value":"35","type":"background-position","unit":"%"},{"type":"whitespace"},{"name":"url","type":"background-image","arguments":[{"type":"css-string","value":"\"..\/..\/media\/examples\/lizard.png\""}]}]',
            json_encode(Value::parse('center / contain no-repeat url("../../media/examples/firefox-logo.svg"),
            #eee 35% url("../../media/examples/lizard.png")  ', 'background' , true, '', '', true))];

        $data[] = [
            '[{"value":"transparent","type":"background-color","colorType":"hex"},{"value":"!important","type":"css-string"}]',
            json_encode(Value::parse('transparent !important  ', 'background' , true, '', '', true))];

        $data[] = [
            '#0000!important',
            Value::renderTokens(Value::parse('transparent !important  ', 'background', true, '', '', true))];

        /*
         * , ],
             ['background-size', 'cover, contain'],
             ['background-image', 'url("../../media/examples/lizard.png"), url("../../media/examples/firefox-logo.svg")'],
             ['background-repeat', 'repeat, repeat'],
             ['background-size', 'auto, auto'],
             ['background-color', 'blue, red'],
             ['background-size', 'auto 10%, 25% auto'],
             ['background-image', 'none, none'],
             ['background-size', 'auto, auto'],
         */
        $property = new PropertyList();

        $property->set('background', 'center / contain no-repeat url("../../media/examples/firefox-logo.svg"),
            #eee 17% url("../../media/examples/lizard.png") ');

        $data[] = [
            'background: center/contain no-repeat url("../../media/examples/firefox-logo.svg"), #eee 17% url("../../media/examples/lizard.png")',
            (string) $property
        ];

        $property->set('background-size', 'cover, contain' );

        $data[] = [
            'background: url("../../media/examples/firefox-logo.svg") center/cover no-repeat, url("../../media/examples/lizard.png") #eee 17%/contain',
            (string) $property
        ];

        $property->set('background-image', ' url("../../media/examples/lizard.png"), url("../../media/examples/firefox-logo.svg")' );

        $data[] = [
            'background: url("../../media/examples/lizard.png") center/cover no-repeat, url("../../media/examples/firefox-logo.svg") #eee 17%/contain',
            (string) $property
        ];

        $property->set('background-repeat', ' repeat, repeat' );

        $data[] = [
            'background: url("../../media/examples/lizard.png") center/cover, url("../../media/examples/firefox-logo.svg") #eee 17%/contain',
            (string) $property
        ];

        $property->set('background-size', 'auto, auto' );

        $data[] = [
            'background: url("../../media/examples/lizard.png") center, url("../../media/examples/firefox-logo.svg") #eee 17%',
            (string) $property
        ];

        $property->set('background-color', 'blue, red' );

        $data[] = [
            'background: url("../../media/examples/lizard.png") blue center, url("../../media/examples/firefox-logo.svg") red 17%',
            (string) $property
        ];

        $property->set('background-size', 'auto 10%, 25% auto' );

        $data[] = [
            'background: url("../../media/examples/lizard.png") blue center/auto 10%, url("../../media/examples/firefox-logo.svg") red 17%/25%',
            (string) $property
        ];

        $property->set('background-image', 'none, none' );

        $data[] = [
            'background: blue center/auto 10%, red 17%/25%',
            (string) $property
        ];

        $property->set('background-size', 'auto, auto' );

        $data[] = [
            'background: blue center, red 17%',
            (string) $property
        ];

        return $data;
    }

    public function backgroundComputeProvider() {

        $data = [];

        $property = new PropertyList();

        $property->set('background', 'none');
        $data[] = [

            'background: none',
            (string) $property
        ];

        $property->set('background', ' #353677');
        $data[] = [

            'background: #353677',
            (string) $property
        ];

        $property->set('background-repeat', ' repeat');
        $data[] = [

            'background: #353677',
            (string) $property
        ];

        $property->set('background-image', '  url(logo.png)');
        $data[] = [

            'background: url(logo.png) #353677',
            (string) $property
        ];

        $property->set('background-repeat', ' repeat-y');
        $data[] = [

            'background: url(logo.png) #353677 repeat-y',
            (string) $property
        ];

        $property->set('background-size', ' cover');
        $data[] = [

            'background: url(logo.png) #353677 repeat-y;
background-size: cover',
            (string) $property
        ];

        $property->set('background-position', ' 0 0');
        $data[] = [

            'background: url(logo.png) #353677 0 0/cover repeat-y',
            (string) $property
        ];

        $property->set('background-attachment', ' scroll');
        $data[] = [

            'background: url(logo.png) #353677 0 0/cover repeat-y',
            (string) $property
        ];

        $property->set('background-attachment', ' fixed');
        $data[] = [

            'background: url(logo.png) #353677 0 0/cover repeat-y fixed',
            (string) $property
        ];

        $property->set('background-origin', ' border-box');
        $data[] = [

            'background: url(logo.png) #353677 0 0/cover repeat-y fixed border-box',
            (string) $property
        ];

        $property->set('background-clip', ' text');
        $data[] = [

            'background: url(logo.png) #353677 0 0/cover repeat-y fixed text border-box',
            (string) $property
        ];

        $property->set('background-color', ' transparent');
        $data[] = [

            'background: url(logo.png) 0 0/cover repeat-y fixed text border-box',
            (string) $property
        ];

        $property->set('background-color', ' yellow');
        $data[] = [

            'background: url(logo.png) #ff0 0 0/cover repeat-y fixed text border-box',
            (string) $property
        ];

        $property->set('background-clip', ' border-box');
        $data[] = [

            'background: url(logo.png) #ff0 0 0/cover repeat-y fixed border-box',
            (string) $property
        ];

        $property->set('background-origin', ' padding-box');
        $data[] = [

            'background: url(logo.png) #ff0 0 0/cover repeat-y fixed',
            (string) $property
        ];

        $property->set('background-attachment', ' scroll');
        $data[] = [

            'background: url(logo.png) #ff0 0 0/cover repeat-y',
            (string) $property
        ];

        $property->set('background-repeat', ' repeat');
        $data[] = [

            'background: url(logo.png) #ff0 0 0/cover',
            (string) $property
        ];

        $property->set('background-size', ' auto');
        $data[] = [

            'background: url(logo.png) #ff0 0 0',
            (string) $property
        ];

        return $data;
    }
}

