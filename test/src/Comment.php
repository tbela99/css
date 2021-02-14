<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Compiler;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

final class Comment extends TestCase
{
    /**
     * @param Compiler $compiler
     * @param $content
     * @param string $expected
     * @throws Exception
     * @dataProvider testLicenseCommentsProvider
     */
    public function testLicenseComments($content, $expected): void
    {
        $this->assertEquals(
            $expected,
            $content
        );
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

        return $data;
    }
}

