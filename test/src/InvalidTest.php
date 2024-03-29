<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

final class InvalidTest extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider recoverProvider
     */
    public function testRecover($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param $actual
     * @param Parser $parser
     * @return void
     * @throws Parser\SyntaxError
     * @dataProvider exceptionProvider
     */
    public function testException($actual, Parser $parser): void
    {

        $this->expectException(Parser\SyntaxError::class);
        $parser->setContent($actual)->getAst();
    }

    public function recoverProvider() {

        $data = [];

        $data[] = ['.foo {

}', (string) (new Parser('
.foo { '))];
        $data[] = ['.foo {

}', (string) (new Parser('
.foo { ;'))];

        $data[] = ['.foo {
 transform: translate(50px)
}', (string) (new Parser('
.foo { transform: translate(50px'))];

        $data[] = ['.foo {
 content: bar
}', (string) (new Parser('
.foo { content: "bar
'))];

        $data[] = ['.foo {
 content: "bar bar"
}', (string) (new Parser('
.foo { content: "bar bar
'))];

        $data[] = ['@media screen {
 .green {
  transform: translate(50px)
 }
}', (string) (new Parser('
@media screen { 

 .green {
 
    transform: translate(50px
'))];

        $data[] = ['@media screen {
 .green {
  transform: translate(50px)
 }
}', (string) (new Parser('
@media screen { 

 .green {
 
    transform: translate(50px /* comment is invalid
'))];

        $data[] = ['@media screen {
 .green {
  transform: translate(50px)
 }
}', (string) (new Parser('
@media screen { 

 .green {
 
    transform: translate(50px) ; /* comment is invalid
'))];

        $data[] = ['.foo {
 name: "jame barr";
 content: "bar bar;"
}', (string) (new Parser('
.foo { 
name: "jame barr";;
content: "bar bar;
'))];

        $data[] = ['.foo {
 content: "bar bar;"
}', (string) (new Parser('
.foo { content: "bar bar;
'))];

        $data[] = ['@media screen {
 .green {
  transform: translate(50px)
 }
}', (string) (new Parser('

@media screen { 

 .green {
 
    transform: translate(50px /* comment
    ;'))];

        $data[] = ['.selector {
 color: green
}', (string) (new Parser('
@ media screen { 

 .green {
 
    transform: translate(50px)
}
}

.selector {

    color: green;
'))];

        return $data;
    }


    public function exceptionProvider() {

        $data = [];

        $parser = new Parser('', [
            'capture_errors' => false
        ]);

        $data[] = ['.foo { transform: translate(50px;', $parser];
        $data[] = ['
@media screen { 

 .green {
 
    transform: translate(50px
    ;
', $parser];

        $data[] = ['
@media screen { 

 .green {
 
    transform: translate(50px
    ; /* comment
', $parser];

        $data[] = ['
@ media screen { 

 .green {
 
    transform: translate(50px)
}
}
', $parser];

        $data[] = ['
@charset "utf-8";
', $parser];

        $data[] = ["@charset 'utf-8';
", $parser];

        $data[] = ['
        * {
            box-sizing: border-box;
        }
        
        @import "foo.css";
', $parser];

        return $data;
    }
}

