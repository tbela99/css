<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

require_once __DIR__.'/../bootstrap.php';

final class ParseTest extends TestCase
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

        $data[] = ['.el {
 margin: 10px calc(2vw + 5px);
 border-radius: 15px calc(15px/3) 4px 2px;
 transition: transform calc(1s - 120ms);
 /* Nope! */
 counter-reset: calc("My " + counter)
}
.el::before {
 /* Nope! */
 content: calc("Candyman " * 3)
}
.el {
 /* This */
 /* Is very different from this */
 width: calc((100% + 2rem)/2)
}',
            (string) (new Parser('.el {
  margin: 10px calc(2vw + 5px);
  border-radius: 15px calc(15px/3) 4px 2px;
  transition: transform calc(1s - 120ms);
}

.el {
  /* Nope! */
  counter-reset: calc("My " + "counter");
}
.el::before {
  /* Nope! */
  content: calc("Candyman " * 3);
}
.el {
  width: calc(
    100%     /   3
  );
}

.el {
  width: calc(
    calc(100% / 3)
    -
    calc(1rem * 2)
  );
}
.el {
  width: calc(
   (100% / 3)
    -
   (1rem * 2)
  );
}
.el {
  width: calc(100% / 3 - 1rem * 2);
}
.el {
  /* This */
  width: calc(100% + 2rem / 2);

  /* Is very different from this */
  width: calc((100% + 2rem) / 2);
}

'))->parse()];

        $data[] = ['#test .test2 {

}
#test3 .test4 {

}', (string) (new Parser('#test .test2{}#test3 .test4{}'))->parse()];

        $data[] = [(string) (new Parser('#test .test2{}#test3 .test4{color:scroll;}'))->parse(), '#test .test2 {

}
#test3 .test4 {
 color: scroll
}'];

        $data[] = ['div[data-elem-id="1587819338886"] {
 color: #000;
 z-index: 5;
 top: calc(50vh - 375px + 325px);
 left: calc(50% - 600px + 26px);
 width: 610px;
 background: red
}', (string) (new Parser('div[data-elem-id="1587819338886"] {
	color: #000000;
	z-index: 5;
	top: calc(50vh - 375px + 325px);
	left: calc(50% - 600px + 26px);
	width: 610px;
	background: red;
}
'))->parse()];
        $data[] = ['div[data-elem-id="1587819338886"]{color:#000;z-index:5;top:calc(50vh - 375px + 325px);left:calc(50% - 600px + 26px);width:610px;background:red}',
            (new Renderer(['compress' => true]))->render((new Parser('div[data-elem-id="1587819338886"] {
	color: #000000;
	z-index: 5;
	top: calc(50vh - 375px + 325px);
	left: calc(50% - 600px + 26px);
	width: 610px;
	background: red;
}
'))->parse())];

        $data[] = ['div+div[data-elem-id="1587819338886"] {
 color: #000;
 z-index: 5;
 top: calc(50vh - 375px + 325px);
 left: calc(50% - 600px + 26px);
 width: 610px;
 background: red
}',
            (string) (new Parser('div + div[data-elem-id="1587819338886"] {
	color: #000000;
	z-index: 5;
	top: calc(50vh - 375px + 325px);
	left: calc(50% - 600px + 26px);
	width: 610px;
	background: red;
}
'))->parse()];
        $data[] = ['div+div[data-elem-id="1587819338886"]{color:#000;z-index:5;top:calc(50vh - 375px + 325px);left:calc(50% - 600px + 26px);width:610px;background:red}', (new Renderer(['compress' => true]))->render((new Parser('div + div[data-elem-id="1587819338886"] {
	color: #000000;
	z-index: 5;
	top: calc(50vh - 375px + 325px);
	left: calc(50% - 600px + 26px);
	width: 610px;
	background: red;
}
'))->parse())];

        $data[] = ['.el{margin:10px calc(2vw + 5px);border-radius:15px calc(15px/3) 4px 2px;transition:transform calc(1s - .12s);counter-reset:calc("My " + counter)}.el::before{content:calc("Candyman " * 3)}.el{width:calc((100% + 2rem)/2)}',
            (new Renderer(['compress' => true]))->render((new Parser('.el {
  margin: 10px calc(2vw + 5px);
  border-radius: 15px calc(15px / 3) 4px 2px;
  transition: transform calc(1s - 120ms);
}

.el {
  /* Nope! */
  counter-reset: calc("My " + "counter");
}
.el::before {
  /* Nope! */
  content: calc("Candyman " * 3);
}
.el {
  width: calc(
    100%     /   3
  );
}

.el {
  width: calc(
    calc(100% / 3)
    -
    calc(1rem * 2)
  );
}
.el {
  width: calc(
   (100% / 3)
    -
   (1rem * 2)
  );
}
.el {
  width: calc(100% / 3 - 1rem * 2);
}
.el {
  /* This */
  width: calc(100% + 2rem / 2);

  /* Is very different from this */
  width: calc((100% + 2rem) / 2);
}

'))->parse())];

        // preserver @charset
        $parser =  (new \TBela\CSS\Parser("@charset \"utf-8\"; @font-face{font-family:'CenturyGothic';src:url('/CenturyGothic.woff') format('woff');font-weight:400;}", ['capture_errors' => false])) /* ->load('template.min.css') */;

        $data[] = [
            '@charset "utf-8";
@font-face {
 font-family: CenturyGothic;
 font-weight: 400;
 src: url(/CenturyGothic.woff) format("woff")
}',
            (new Renderer(['charset' => true]))->renderAst($parser)
        ];
        return $data;
    }
}

