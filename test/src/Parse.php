<?php

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

final class Parse extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider parseProvider
     */
    public function testParse($expected, $actual)
    {

        $this->assertEquals(
            $expected,
          $actual
        );
    }


    public function parseProvider() {

        $data = [];

        $data[] = [(string) (new Parser('#test .test2{}#test3 .test4{}'))->parse(), '#test .test2 {

}
#test3 .test4 {

}'];

        $data[] = [(string) (new Parser('#test .test2{}#test3 .test4{color:scroll;}'))->parse(), '#test .test2 {

}
#test3 .test4 {
 color: scroll
}'];

        return $data;
    }
}

