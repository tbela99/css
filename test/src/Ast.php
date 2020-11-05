<?php

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
    public function testIdentifier($expected, $actual)
    {

        $this->assertEquals(
            $expected,
          $actual
        );
    }


    public function identifierProvider() {

        $data = [];
//        $parser = (new TBela\CSS\Parser('', [
//            'allow_duplicate_rules' => false,
//    'allow_duplicate_declarations' => false
//        ]))->load(__DIR__.'/../css/color.css');
//
//        $data[] = [(string) $parser, (string) $parser->parse()];
//
//        $parser = new TBela\CSS\Parser('div[data-elem-id="1587819236980"], a {
// background: red
//}');
//
//        $data[] = [(string) $parser, (string) $parser->parse()];
        $parser = new TBela\CSS\Parser('
 * {
  text-shadow: none!important /* comment 7 */;
 }
');

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


        $data[] = [(string) $parser, (string) $parser->parse()];

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
        $data[] = [(string) $parser, (string) $parser->parse()];

        $parser->load(__DIR__.'/../css/template.css');
        $data[] = [(string) $parser, (string) $parser->parse()];

        $parser->setOptions(['sourcemap' => true, 'flatten_import' => true])->load(__DIR__.'/../files/test_2.css');

        $data[] = [

            file_get_contents(__DIR__.'/../files/test_2_parsed_comments.css'),
            (string) $parser->parse()
        ];
        return $data;
    }

    public function identifierAstProvider() {

        $data = [];

        $data[] = ['div[data-elem-id="1587819236980"] {
 background: red
}', (new Renderer())->renderAst((new Parser('div[data-elem-id="1587819236980"]{
background:red;
}'))->getAst()) ];

        $data[] = [ 'div[data-elem-id=a1587819236980] {
 background: red
}', (new Renderer())->renderAst((new Parser('div[data-elem-id="a1587819236980"]{
background:red;
}'))->getAst()) ];

        return $data;
    }
}

