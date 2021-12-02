<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

final class NestingRule extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider testNestingRuleProvider
     */
    public function testNestingRule($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider testNestingAtRuleProvider
     */
    public function testNestingAtRule($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider testAtRuleProvider
     */
    public function testAtRule($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider testNestingMediaRuleProvider
     */
    public function testNestingMediaRule($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    public function testNestingRuleProvider()
    {

        $data = [];

        $parser = (new Parser())->load(__DIR__ . '/../nested/nested.css');
        $renderer = new Renderer(['legacy_rendering' => true]);

        $data[] = ['/* this row */
table.colortable {
 /* clean all */
 text-shadow: none;
 color /* hidden */: blue /* blues */
}
table.colortable td {
 text-align: center
}
table.colortable td.c {
 text-transform: uppercase
}
table.colortable td:first-child,
table.colortable td:first-child+td {
 border: 1px solid #000
}
table.colortable th {
 text-align: center;
 background: #000;
 color: #fff
}
.foo {
 padding: 2ch
}
.foo {
 color: blue
}
.foo.foo {
 padding: 2ch
}
/* The parent selector can be arbitrarily complicated */
:is(.error, #404):hover > .baz {
 color: red
}', $renderer->setOptions(['legacy_rendering' => true])->renderAst($parser)];

        $data[] = ['/* this row */
table.colortable {
 /* clean all */
 text-shadow: none;
 color /* hidden */: blue /* blues */;
 & td {
  text-align: center;
  &.c {
   text-transform: uppercase
  }
  &:first-child,
  &:first-child+td {
   border: 1px solid #000
  }
 }
 & th {
  text-align: center;
  background: #000;
  color: #fff
 }
}
.foo {
 color: blue;
 & {
  padding: 2ch
 }
}
.foo {
 color: blue;
 && {
  padding: 2ch
 }
}
/* The parent selector can be arbitrarily complicated */
.error,
#404 {
 &:hover > .baz {
  color: red
 }
}', $renderer->setOptions(['legacy_rendering' => false])->renderAst($parser)];

        return $data;
    }

    public function testNestingAtRuleProvider()
    {

        $data = [];

        $parser = (new Parser())->load(__DIR__ . '/../nested/rule.css');
        $renderer = new Renderer(['legacy_rendering' => true]);

        $data[] = ['.foo {
 color: red
}
.parent .foo,
p .foo {
 color: blue
}
.foo {
 color: blue
}
.bar .foo {
 color: red
}
.bar .foo.baz {
 color: green
}', $renderer->renderAst($parser)];

        $data[] = ['.foo {
 color: red;
 @nest .parent &,
 p & {
  color: blue
 }
}
.foo {
 color: blue;
 @nest .bar & {
  color: red;
  &.baz {
   color: green
  }
 }
}', $renderer->setOptions(['legacy_rendering' => false])->renderAst($parser)];

        return $data;
    }

    public function testAtRuleProvider()
    {

        $data = [];

        $parser = (new Parser())->load(__DIR__ . '/../nested/at-rule.css');
        $renderer = new Renderer(['legacy_rendering' => true]);

        $data[] = ['@media (min-width:540px) {
 div p {
  line-height: 24px
 }
 div p span {
  line-height: 1.2
 }
}
.foo {
 display: grid
}
@media (orientation:landscape) {
 .foo {
  grid-auto-flow: column
 }
}', $renderer->renderAst($parser)];

        $data[] = ['div {
 @media (min-width:540px) {
  & p {
   line-height: 24px;
   & span {
    line-height: 1.2
   }
  }
 }
}
.foo {
 display: grid;
 @media (orientation:landscape) {
  grid-auto-flow: column
 }
}', $renderer->setOptions(['legacy_rendering' => false])->renderAst($parser)];

        return $data;
    }

    public function testNestingMediaRuleProvider()
    {

        $data = [];

        $parser = (new Parser())->load(__DIR__ . '/../nested/at-rule2.css');
        $renderer = new Renderer(['legacy_rendering' => true]);

        $data[] = ['.foo {
 display: grid
}
.foo span {
 display: inline-block
}
@media (orientation:portrait) {
 .foo {
  grid-auto-flow: column
 }
}
@media (orientation:portrait)and(min-inline-size > 1024px) {
 .foo {
  max-inline-size: 1024px
 }
}
@media (orientation:portrait)and(min-inline-size > 1024px)and(min-width:1024px) {
 .foo {
  whitespace: wrap
 }
}', $renderer->renderAst($parser)];

        $data[] = ['.foo {
 display: grid;
 & span {
  display: inline-block
 }
 @media (orientation:portrait) {
  grid-auto-flow: column;
  @media (min-inline-size > 1024px) {
   max-inline-size: 1024px;
   @media (min-width:1024px) {
    whitespace: wrap
   }
  }
 }
}', $renderer->setOptions(['legacy_rendering' => false])->renderAst($parser)];

        return $data;
    }
}

