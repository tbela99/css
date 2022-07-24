<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Ast\Traverser;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

final class AstTraverserTest extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider traverseProvider
     */
    public function testTraverse($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
          $actual
        );
    }

    public function traverseProvider() {

        $data = [];
        
        $parser = (new Parser())->load(__DIR__.'/../ast/media.css');
        $traverser = new Traverser();
        $renderer = new Renderer(['remove_empty_nodes' => true]);

        $ast = $parser->getAst();

        // remove @media print
        $traverser->on('enter', function ($node) {

            if ($node->type == 'AtRule' && $node->name == 'media' && $node->value == 'print') {

                return Traverser::IGNORE_NODE;
            }
        });

        $data[] = ['/* this comment is here */
@media screen {
 body {
  font-size: 13px
 }
}
@media screen, print {
 body {
  line-height: 1.2
 }
}
@media only screen and (min-width:320px) and (max-width:480px) and (resolution:150dpi) {
 body {
  line-height: 1.4
 }
}
@media (height > 600px) {
 body {
  line-height: 1.4
 }
}
@media (400px <= width <= 700px) {
 body {
  line-height: 1.4
 }
}', $renderer->renderAst($traverser->traverse($ast))];

    // remove @media print
    // remove comment
    $traverser->on('enter', function ($node) {

        if ($node->type == 'Comment') {

            return Traverser::IGNORE_NODE;
        }
    });

        $data[] = ['@media screen {
 body {
  font-size: 13px
 }
}
@media screen, print {
 body {
  line-height: 1.2
 }
}
@media only screen and (min-width:320px) and (max-width:480px) and (resolution:150dpi) {
 body {
  line-height: 1.4
 }
}
@media (height > 600px) {
 body {
  line-height: 1.4
 }
}
@media (400px <= width <= 700px) {
 body {
  line-height: 1.4
 }
}', $renderer->renderAst($traverser->traverse($ast))];

// remove line-height
$traverser->off('enter')->on('enter', function ($node) {

    // remove 'line-height'
    if ($node->type == 'Declaration' && $node->name == 'line-height') {

        return Traverser::IGNORE_NODE;
    }
});

        $data[] = ['/* this comment is here */
@media print {
 body {
  font-size: 10pt
 }
}
@media screen {
 body {
  font-size: 13px
 }
}', $renderer->renderAst($traverser->traverse($ast))];
        return $data;
    }
}

