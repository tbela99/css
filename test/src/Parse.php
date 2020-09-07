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

        $data[] = [(string) (new Parser('.el {
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
@media (min-width: calc(40rem + 1px)) {
  /* Wider than 40rem */
  transform: rotate(calc(1turn + 45deg));

animation-delay: calc(1s + 15ms);
}

'))->parse(), '.el {
 margin: 10px calc(2vw + 5px);
 border-radius: 15px calc(15px / 3) 4px 2px;
 transition: transform calc(1s - 120ms);
 /* Nope! */
 counter-reset: calc("My " + counter)
}
.el::before {
 /* Nope! */
 content: calc("Candyman " * 3)
}
.el {
 width: calc((100% + 2rem) / 2);
 /* This */
 /* Is very different from this */
}
@media (min-width:calc(40rem + 1px)) {
  /* Wider than 40rem */
  transform: rotate(calc(1turn + 45deg));
  animation-delay: calc(1s + 15ms)
}'];

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

        $data[] = [(new Renderer(['compress' => true]))->render((new Parser('.el {
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
@media (min-width: calc(40rem + 1px)) {
  /* Wider than 40rem */
  transform: rotate(calc(1turn + 45deg));

animation-delay: calc(1s + 15ms);
}

'))->parse()), '.el{margin:10px calc(2vw + 5px);border-radius:15px calc(15px / 3) 4px 2px;transition:transform calc(1s - .12s);counter-reset:calc("My " + counter)}.el::before{content:calc("Candyman " * 3)}.el{width:calc((100% + 2rem) / 2)}@media(min-width:calc(40rem + 1px)){transform:rotate(calc(1turn + 45deg));animation-delay:calc(1s + 15ms)}'];

        return $data;
    }
}

