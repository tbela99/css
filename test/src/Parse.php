<?php
declare(strict_types=1);

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
    public function testParse($expected, $actual): void
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

        $data[] = [(string) (new Parser('div[data-elem-id="1587819338886"] {
	color: #000000;
	z-index: 5;
	top: calc(50vh - 375px + 325px);
	left: calc(50% - 600px + 26px);
	width: 610px;
	background: red;
}
'))->parse(), 'div[data-elem-id="1587819338886"] {
 color: #000;
 z-index: 5;
 top: calc(50vh - 375px + 325px);
 left: calc(50% - 600px + 26px);
 width: 610px;
 background: red
}'];
        $data[] = [(new Renderer(['compress' => true]))->render((new Parser('div[data-elem-id="1587819338886"] {
	color: #000000;
	z-index: 5;
	top: calc(50vh - 375px + 325px);
	left: calc(50% - 600px + 26px);
	width: 610px;
	background: red;
}
'))->parse()), 'div[data-elem-id="1587819338886"]{color:#000;z-index:5;top:calc(50vh - 375px + 325px);left:calc(50% - 600px + 26px);width:610px;background:red}'];

        $data[] = [(string) (new Parser('div + div[data-elem-id="1587819338886"] {
	color: #000000;
	z-index: 5;
	top: calc(50vh - 375px + 325px);
	left: calc(50% - 600px + 26px);
	width: 610px;
	background: red;
}
'))->parse(), 'div+div[data-elem-id="1587819338886"] {
 color: #000;
 z-index: 5;
 top: calc(50vh - 375px + 325px);
 left: calc(50% - 600px + 26px);
 width: 610px;
 background: red
}'];
        $data[] = [(new Renderer(['compress' => true]))->render((new Parser('div + div[data-elem-id="1587819338886"] {
	color: #000000;
	z-index: 5;
	top: calc(50vh - 375px + 325px);
	left: calc(50% - 600px + 26px);
	width: 610px;
	background: red;
}
'))->parse()), 'div+div[data-elem-id="1587819338886"]{color:#000;z-index:5;top:calc(50vh - 375px + 325px);left:calc(50% - 600px + 26px);width:610px;background:red}'];

        return $data;
    }
}

