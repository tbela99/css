<?php

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;
use TBela\CSS\Parser\SyntaxError;
use TBela\CSS\Renderer;

final class Comment extends TestCase
{
    /**
     * @param string $content
     * @param string $expected
     * @throws Exception
     * @dataProvider testLicenseCommentsProvider
     */
    public function testLicenseComments($content, $expected)
    {
        $this->assertEquals(
            $expected,
            $content
        );
    }

    /**
     * @param $content
     * @return void
     * @dataProvider testCdoCdcExceptionsProvider
     *
     */
    public function testCDOCDCExceptions($content)
    {
        $this->expectException(Parser\SyntaxError::class);

        (new Parser($content, [
            'capture_errors' => false
        ]))->getAst();
    }

    public function testLicenseCommentsProvider () {

        $renderer = new Renderer([
            'preserve_license' => true,
            'remove_comments' => true,
            'compress' => false
        ]);

        $element = (new Parser())->load(__DIR__.'/../query/comments.css')->parse();

        $data = [];

        $data[] = [$renderer->render($element),
            file_get_contents(__DIR__.'/../query/comments_parsed.css')
        ];

        $data[] = [$renderer->setOptions(([
            'preserve_license' => true,
            'remove_comments' => true,
            'compress' => true
        ]))->render($element),
            file_get_contents(__DIR__.'/../query/comments_parsed.min.css')
        ];

        $data[] = [$renderer->setOptions(([
            'preserve_license' => false,
            'remove_comments' => true,
            'compress' => false
        ]))->render($element),
            file_get_contents(__DIR__.'/../query/comments_all.css')
        ];

        $data[] = [$renderer->setOptions(([
            'preserve_license' => false,
            'remove_comments' => true,
            'compress' => true
        ]))->render($element),
            file_get_contents(__DIR__.'/../query/comments_all.min.css')
        ];

        $data[] = [$renderer->setOptions(([
            'preserve_license' => false,
            'remove_comments' => true,
            'compress' => true
        ]))->render($element),
            file_get_contents(__DIR__.'/../query/comments_all.min.css')
        ];

        $data[] = [$renderer->setOptions(([
            'compress' => false,
            'preserve_license' => true,
            'remove_comments' => false
        ]))->renderAst(new Parser('
<!-- test -->body {
 display: grid;
 grid-template: main auto aside auto/100%
}
<!-- test 2 -->
@media (width > 40em) {
 body {
  grid-template: "aside main" auto/1fr 3fr
 }
<!-- test 3 -->')),
            'body {
 display: grid;
 grid-template: main auto aside auto/100%
}
@media (width > 40em) {
 body {
  grid-template: "aside main" auto/1fr 3fr
 }
}'
        ];

        $data[] = [$renderer->setOptions(([
            'compress' => true,
            'preserve_license' => true,
            'remove_comments' => false
        ]))->renderAst(new Parser('
<!-- test -->body {
 display: grid;
 grid-template: main auto aside auto/100%
}
<!-- test 2 -->
@media (width > 40em) {
 body {
  grid-template: "aside main" auto/1fr 3fr
 }
<!-- test 3 -->')),
            'body{display:grid;grid-template:main auto aside auto/100%}@media(width > 40em){body{grid-template:"aside main" auto/1fr 3fr}}'
        ];


        $data[] = [$renderer->setOptions(([
            'compress' => true,
            'preserve_license' => true,
            'remove_comments' => false
        ]))->renderAst(new Parser('
<!-- test -->body {
 display: grid;
 grid-template: main auto aside auto/100%
}
<!-- test 2 -->
@media (width > 40em) {
 body {
  grid-template: "aside main" auto/1fr 3fr
 }
<!-- test 3 -->')),
            'body{display:grid;grid-template:main auto aside auto/100%}@media(width > 40em){body{grid-template:"aside main" auto/1fr 3fr}}'
        ];


        $data[] = [$renderer->setOptions(([
            'compress' => true,
            'preserve_license' => true,
            'remove_comments' => false
        ]))->renderAst(new Parser('
body {
 display: grid;<!-- test -->
 grid-template: main auto aside auto/100%
}
@media (width > 40em) {
<!-- test 2 -->
 body {
<!-- test 3 -->
  grid-template: "aside main" auto/1fr 3fr')),
            'body{display:grid;grid-template:main auto aside auto/100%}@media(width > 40em){body{grid-template:"aside main" auto/1fr 3fr}}'
        ];


        $data[] = [$renderer->setOptions(([
            'compress' => true,
            'preserve_license' => true,
            'remove_comments' => false
        ]))->renderAst(new Parser('
body {
 display: grid;<!-- test -->
 grid-template: main auto aside auto/100%
}
@media (width > 40em) {
<!-- test 2 -->
 body {
<!-- test 3 -->
  grid-template: "aside main" auto/1fr 3fr')),
            'body{display:grid;grid-template:main auto aside auto/100%}@media(width > 40em){body{grid-template:"aside main" auto/1fr 3fr}}'
        ];

        return $data;
    }

    public function testCdoCdcExceptionsProvider() {

        $data = [];

        $data[] = ['body {
 display: grid;<!-- test -->
 grid-template: main auto aside auto/100%
}'];
        $data[] = ['media (width > 40em) {
<!-- test 2 -->
 body {
<!-- test 3 -->
  grid-template: "aside main" auto/1fr 3fr'];

        return $data;
    }
}

