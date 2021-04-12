<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

final class Sourcemap extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider testSourcemapProvider
     */
    public function testSourcemap($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
          $actual
        );
    }

    public function testSourcemapProvider() {

        $data = [];

        $ast = (new Parser())->load(__DIR__ . '/../sourcemap/sourcemap.css')->
        append(__DIR__ . '/../sourcemap/sourcemap.2.css')->
        append(__DIR__ . '/../sourcemap/sourcemap.media.css')->getAst();

        $renderer = new Renderer([
            'sourcemap' => true
        ]);

        $outFile = __DIR__.'/../sourcemap/generated/sourcemap.generated.css';
        $renderer->save($ast, $outFile);

        $data[] = ['{
    "version": 3,
    "file": "",
    "sourceRoot": "",
    "sources": [],
    "names": [],
    "mappings": ""
}', file_get_contents($outFile.'.map')];

        $outFile = __DIR__.'/../sourcemap/generated/sourcemap.generated.min.css';

        $renderer->setOptions([
            'compress' => true
        ])->save($ast, $outFile);

        $data[] = ['{
    "version": 3,
    "file": "",
    "sourceRoot": "",
    "sources": [],
    "names": [],
    "mappings": ""
}', file_get_contents($outFile.'.map')];

        return $data;
    }
}

