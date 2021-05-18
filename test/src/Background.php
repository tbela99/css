<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;
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
    public function testbackgroundAttachment($expected, $actual): void
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
    public function testbackgroundRepeat($expected, $actual): void
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
    public function testbackgroundPosition($expected, $actual): void
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

        $data[] = [
            '[{"value":"left","type":"background-position-left"},{"type":"whitespace"},{"value":"20","type":"background-position-left","unit":"%"},{"type":"whitespace"},{"value":"center","type":"background-position-top"},{"type":"whitespace"},{"value":"15","type":"background-position-top","unit":"%"}]',
            json_encode(array_map(function ($value) {

                return $value->getData();
            }, Value::parse('left 20% center 15%', 'background-position')->toArray()))];

        $data[] = [
            '[{"value":"left","type":"background-position-left"},{"type":"whitespace"},{"value":"center","type":"background-position-top"},{"type":"whitespace"},{"value":"15","type":"background-position-top","unit":"%"}]',
            json_encode(array_map(function ($value) {

                return $value->getData();
            }, Value::parse('left  center 15%', 'background-position')->toArray()))];

        $data[] = [
            '[{"value":"left","type":"background-position-left"},{"type":"whitespace"},{"value":"bottom","type":"background-position-top"},{"type":"whitespace"},{"value":"15","type":"background-position-top","unit":"%"}]',
            json_encode(array_map(function ($value) {

                return $value->getData();
            }, Value::parse('left  bottom 15%', 'background-position')->toArray()))];

        $data[] = [
            '[{"value":"left","type":"background-position-left"},{"type":"whitespace"},{"value":"15","type":"background-position-left","unit":"%"},{"type":"whitespace"},{"value":"bottom","type":"background-position-top"}]',
            json_encode(array_map(function ($value) {

                return $value->getData();
            }, Value::parse('left  15% bottom', 'background-position')->toArray()))];

        $data[] = [
            '[{"value":"left","type":"background-position-left"},{"type":"whitespace"},{"value":"center","type":"background-position-top"}]',
            json_encode(array_map(function ($value) {

                return $value->getData();
            }, Value::parse('left center', 'background-position')->toArray()))];

        $data[] = [
            '[{"value":"left","type":"background-position-left"},{"type":"whitespace"},{"value":"15","type":"background-position-top","unit":"%"}]',
            json_encode(array_map(function ($value) {

                return $value->getData();
            }, Value::parse('left  15%', 'background-position')->toArray()))];

        $data[] = [
            '[{"value":"left","type":"background-position-left"}]',
            json_encode(array_map(function ($value) {

                return $value->getData();
            }, Value::parse('left  ', 'background-position')->toArray()))];

        $data[] = [
            '[{"value":"right","type":"background-position-left"}]',
            json_encode(array_map(function ($value) {

                return $value->getData();
            }, Value::parse('right  ', 'background-position')->toArray()))];

        $data[] = [
            '[{"value":"bottom","type":"background-position-top"}]',
            json_encode(array_map(function ($value) {

                return $value->getData();
            }, Value::parse('bottom  ', 'background-position')->toArray()))];

        return $data;
    }
}

