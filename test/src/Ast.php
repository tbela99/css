<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

final class Ast extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider identifierProvider
     */
    public function testIdentifier($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
          $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider mediaAllProvider
     */
    public function testMediaAll($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    public function identifierProvider() {

        $data = [];
        
        $parser = new TBela\CSS\Parser('
 * {
  text-shadow: none!important /* comment 7 */;
 }
');

        $data[] = [(string) $parser, (string) $parser->parse()];

        $parser->setContent('
	
	a:visited {
		text-decoration: underline;
	}
	');

        $data[] = [(string) $parser, (new Renderer())->renderAst($parser->getAst())];
        $data[] = [(string) $parser, (string) $parser->parse()];

        $parser->setContent('
	a,
	a:visited {
		text-decoration: underline;
	}

	a[href]:after {
		content: " ("attr(href) ")";
	}

	abbr[title]:after {
		content: " ("attr(title) ")";
	}');

        $data[] = [(string) $parser, (new Renderer())->renderAst($parser->getAst())];
        $data[] = [(string) $parser, (string) $parser->parse()];

        $parser->setContent('
@keyframes identifier {
	0% {
		top: 0;
		left: 0;
	}

	30% {
		top: 50px;
	}

	68%,
	72% {
		left: 50px;
	}

	100% {
		top: 100px;
		left: 100%;
	}
}
');

        $data[] = [(string) $parser,
            (new Renderer())->renderAst($parser->getAst())];
        $data[] = [(string) $parser,
            (string) $parser->parse()];

        $parser->setContent('
.btn .caret {
	margin-top: 8px;
	margin-left: 0;
}

.btn-large .caret {
	margin-top: 6px;
}

.btn-large .caret {
	border-left-width: 5px;
	border-right-width: 5px;
	border-top-width: 5px;
}

.btn-mini .caret,
.btn-small .caret {
	margin-top: 8px;
}

.dropup .btn-large .caret {
	border-bottom-width: 5px;
}
');

        $data[] = [(string) $parser, (string) $parser->parse()];

        $parser->load(__DIR__.'/../perf_files/php-net.css');
        $data[] = [(string) $parser,
            (string) $parser->parse()];

        $parser->load(__DIR__.'/../css/template.css');
        $data[] = [(string) $parser, (string) $parser->parse()];

        $parser->setOptions(['flatten_import' => true])->load(__DIR__.'/../files/test_2.css');

        $data[] = [

            file_get_contents(__DIR__.'/../files/test_2_parsed_comments.css'),
            (string) $parser->parse()
        ];

        $data[] = [

            '@keyframes identifier {
 0% {
  top: 0;
  left: 0
 }
 0%,
 100% {
  top: 0;
  left: 0
 }
}',
            (string) (new Parser('@keyframes identifier {
	0% {
		top: 0;
		left: 0;
	}
	0%, 100% {
		top: 0;
		left: 0;
	}
}'))
        ];

        $data[] = [

            '@keyframes identifier{0%{top:0;left:0}0%,100%{top:0;left:0}}',
            (new Renderer(['compress' => true]))->renderAst(new Parser('@keyframes identifier {
	0% {
		top: 0;
		left: 0;
	}
	0%, 100% {
		top: 0;
		left: 0;
	}
}'))
        ];
        return $data;
    }

    public function mediaAllProvider() {

        $data = [];

        $data[] = [

'body {
 font-size: 108px;
 color: #fff;
 text-shadow: 1px 5px 3px #000
}
/*!
* Font Awesome Free 5.12.1 by @fontawesome - https://fontawesome.com
* License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License)
*/
.fa,
.fab,
.fad,
.fal,
.far,
.fas {
 /* don\'t comment */
 -moz-osx-font-smoothing: grayscale;
 -webkit-font-smoothing: antialiased;
 display: inline-block;
 font-style: normal;
 font-variant: normal;
 line-height: 1;
 text-rendering: auto
}
.bg {
 background: no-repeat url(sourcemap/images/bg.png) 50% 50%/cover
}
.fa-bahai {
 display: inline-block
}
.fa-bahai:before {
 content: "s-2 ";
 font-size: 80%
}
body {
 /*font-size: 14px*/
 line-height: 1.3
}',
(string) (new Parser())->load(__DIR__ . '/../sourcemap/sourcemap.css')->
append(__DIR__ . '/../sourcemap/sourcemap.2.css')->
append(__DIR__ . '/../sourcemap/sourcemap.media.css')
     ];

        return $data;
    }
}

