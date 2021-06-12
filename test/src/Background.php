<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;
use TBela\CSS\Property\PropertyList;
use TBela\CSS\Value;

final class Background extends TestCase
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
            json_encode(Value::parse('left ', 'background-position')->toObject())];

        $data[] = [
            '[{"value":"left","type":"background-position"},{"type":"whitespace"},{"value":"center","type":"background-position"}]',
            json_encode(Value::parse('left  center', 'background-position')->toObject())];

        $data[] = [
            '[{"value":"right","type":"background-position"},{"type":"whitespace"},{"value":"center","type":"background-position"},{"type":"whitespace"},{"value":"10","type":"background-position","unit":"%"}]',
            json_encode(Value::parse(' right center 10%  ', 'background-position')->toObject())];

        $data[] = [
            '[{"value":"right","type":"background-position"},{"type":"whitespace"},{"value":"0","type":"background-position"},{"type":"whitespace"},{"value":"bottom","type":"background-position"},{"type":"whitespace"},{"value":"50","type":"background-position","unit":"%"}]',
            json_encode(Value::parse(' right 0 bottom 50%  ', 'background-position')->toObject())];

        return $data;
    }

    public function backgroundImageProvider() {

        $data = [];

        $data[] = [
            '[{"type":"background-image","value":"none"}]',
            json_encode(Value::parse('none', 'background-image')->toObject())];

        $data[] = [
            '[{"name":"url","type":"background-image","arguments":[{"type":"css-string","value":"cat.jpg","q":""}]}]',
            json_encode(Value::parse('url(cat.jpg)', 'background-image')->toObject())];

        return $data;
    }

    public function backgroundOriginProvider() {

        $data = [];

        $data[] = [
            '[{"type":"background-origin","value":"padding-box"}]',
            json_encode(Value::parse('padding-box', 'background-origin')->toObject())];

        $data[] = [
            '[{"type":"background-origin","value":"border-box"}]',
            json_encode(Value::parse('border-box', 'background-origin')->toObject())];

        $data[] = [
            '[{"type":"background-origin","value":"content-box"}]',
            json_encode(Value::parse('content-box', 'background-origin')->toObject())];

        return $data;
    }

    public function backgroundClipProvider() {

        $data = [];

        $data[] = [
            '[{"type":"background-clip","value":"padding-box"}]',
            json_encode(Value::parse('padding-box', 'background-clip')->toObject())];

        $data[] = [
            '[{"type":"background-clip","value":"border-box"}]',
            json_encode(Value::parse('border-box', 'background-clip')->toObject())];

        $data[] = [
            '[{"type":"background-clip","value":"content-box"}]',
            json_encode(Value::parse('content-box', 'background-clip')->toObject())];

        $data[] = [
            '[{"type":"background-clip","value":"text"}]',
            json_encode(Value::parse('text', 'background-clip')->toObject())];

        return $data;
    }

    public function backgroundProvider() {

        $data = [];

        $data[] = [
            '[{"type":"background-repeat","value":"round"}]',
            json_encode(Value::parse('round   round ', 'background')->toObject())];

        $data[] = [
            '[{"name":"url","type":"background-image","arguments":[{"type":"css-string","value":"images\/hero.jpg","q":""}]},{"type":"whitespace"},{"type":"background-repeat","value":"round"}]',
            json_encode(Value::parse('url(images/hero.jpg) round   round ', 'background')->toObject())];

        $data[] = [
            '[{"value":"none","type":"background"}]',
            json_encode(Value::parse('none ', 'background')->toObject())];

        $data[] = [
            '[{"value":"no-repeat","type":"background-repeat","q":""},{"type":"whitespace"},{"name":"url","type":"background-image","arguments":[{"type":"css-string","value":"sourcemap\/images\/bg.png","q":""}]},{"type":"whitespace"},{"value":"50","type":"background-position","unit":"%"},{"type":"whitespace"},{"value":"50","type":"background-position","unit":"%"},{"type":"separator","value":"\/"},{"type":"background-size","value":"cover"}]',
            json_encode(Value::parse('no-repeat url(sourcemap/images/bg.png) 50% 50%/cover ', 'background')->toObject())];

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

