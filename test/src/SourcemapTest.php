<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Exceptions\IOException;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

require_once __DIR__.'/../bootstrap.php';

final class SourcemapTest extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider sourcemapProvider
     */
    public function testSourcemap($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider sourcemapImportProvider
     */
    public function testSourcemapImport($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider sourcemapUrlProvider
     */
    public function testSourcemapUrl($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider sourcemapNestedProvider
     */
    public function testSourcemapNested($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    public function sourcemapProvider() {

        $data = [];

        $element = (new Parser())->load(__DIR__ . '/../fixtures/sourcemap/sourcemap.css')->
        append(__DIR__ . '/../fixtures/sourcemap/sourcemap.2.css')->
        append(__DIR__ . '/../fixtures/sourcemap/sourcemap.media.css');

        $renderer = new Renderer([
            'sourcemap' => true
        ]);

        $outFile = __DIR__.'/../fixtures/sourcemap/generated/sourcemap.generated.test.css';
        $renderer->save($element, $outFile);

        $data[] = ['body {
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
 background: no-repeat url(../images/bg.png) 50% 50%/cover
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
}
',
            preg_replace('#'.preg_quote('/*# sourceMappingURL=', '#').'.*?\*/#', '', file_get_contents($outFile))
        ];

        $data[] = ['AACA;;;;;;;;;AAaA;;;;;;;;;;;;;;;AAgBA;;;AC7BA;;;AAIA;;;;ACFI', json_decode(file_get_contents($outFile.'.map'), true)['mappings']];

        $outFile = __DIR__.'/../fixtures/sourcemap/generated/sourcemap.generated.test.min.css';

        $renderer->setOptions([
            'compress' => true
        ])->save($element, $outFile);

        $data[] = ['AACA,6DAaA,+LAgBA,6DC7BA,+BAIA,8CCFI', json_decode(file_get_contents($outFile.'.map'), true)['mappings']];

        return $data;
    }

    /**
     * @throws IOException
     * @throws Parser\SyntaxError
     */
    public function sourcemapImportProvider() {

        $data = [];

        $element = (new Parser('', [
            'flatten_import' => true,
            'capture_errors' => false
        ]))->load(__DIR__ . '/../fixtures/sourcemap/sourcemap.import.css');

        $renderer = new Renderer([
            'sourcemap' => true
        ]);

        $outFile = __DIR__.'/../fixtures/sourcemap/generated/sourcemap.generated.import.test.css';
        $renderer->save($element, $outFile);

        $data[] = ["/*! this is supposed to be the license. */
body {
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
 /* don't comment */
 -moz-osx-font-smoothing: grayscale;
 -webkit-font-smoothing: antialiased;
 display: inline-block;
 font-style: normal;
 font-variant: normal;
 line-height: 1;
 text-rendering: auto
}
.bg {
 background: no-repeat url(../images/bg.png) 50% 50%/cover
}
/* import the cookie monster file */
.fa-bahai {
 display: inline-block
}
.fa-bahai:before {
 content: \"s-2 \";
 font-size: 80%
}
/* import the media library */
body {
 /*font-size: 14px*/
 line-height: 1.3
}",
            preg_replace('#\n'.preg_quote('/*# sourceMappingURL=', '#').'.*?\*/#', '', file_get_contents($outFile))
        ];

        $data[] = [';AACA;;;;;;;;;AAaA;;;;;;;;;;;;;;;AAgBA;;;;AC7BA;;;AAIA;;;;;ACFI', json_decode(file_get_contents($outFile.'.map'), true)['mappings']];

        $outFile = __DIR__.'/../fixtures/sourcemap/generated/sourcemap.generated.import.test.min.css';

        $renderer->setOptions([
            'compress' => true
        ])->save($element, $outFile);

        $data[] = ['AACA,6DAaA,+LAgBA,6DC7BA,+BAIA,8CCFI', json_decode(file_get_contents($outFile.'.map'), true)['mappings']];

        return $data;
    }

    /**
     * @throws IOException
     * @throws Parser\SyntaxError
     */
    public function sourcemapUrlProvider() {

        $data = [];

        $element = 
$parser = (new Parser('', [
        'flatten_import' => true
]))->load(__DIR__.'/../fixtures/sourcemap/sourcemap-url.css')->parse();

        $renderer = new Renderer([
            'sourcemap' => true
        ]);

        $outFile = __DIR__.'/../fixtures/sourcemap/generated/sourcemap.generated.url.test.css';
        $renderer->save($element, $outFile);

        $data[] = ['/*!
 * Font Awesome Free 5.15.3 by @fontawesome - https://fontawesome.com
 * License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License)
 */
@font-face {
 font-family: "Font Awesome 5 Brands";
 font-style: normal;
 font-weight: 400;
 font-display: block;
 src: url(https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/webfonts/fa-brands-400.eot?#iefix) format("embedded-opentype"), url(https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/webfonts/fa-brands-400.woff2) format("woff2"), url(https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/webfonts/fa-brands-400.woff) format("woff"), url(https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/webfonts/fa-brands-400.ttf) format("truetype"), url(https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/webfonts/fa-brands-400.svg#fontawesome) format("svg")
}
.fab {
 font-family: "Font Awesome 5 Brands";
 font-weight: 400
}
',
            preg_replace('#'.preg_quote('/*# sourceMappingURL=', '#').'.*?\*/#', '', file_get_contents($outFile))
        ];

        $data[] = [';;;;AAIA;;;;;;;AAAgb', json_decode(file_get_contents($outFile.'.map'), true)['mappings']];
        $outFile = __DIR__.'/../fixtures/sourcemap/generated/sourcemap.generated.url.test.min.css';

        $renderer->setOptions([
            'compress' => true
        ])->save($element, $outFile);

        $data[] = ['AAIA,gqBAAgb', json_decode(file_get_contents($outFile.'.map'), true)['mappings']];

        return $data;
    }

    /**
     * @throws IOException|Parser\SyntaxError
     */
    public function sourcemapNestedProvider() {

        $data = [];

        $parser = (new Parser('', [
            'flatten_import' => true,
            'capture_errors' => true
        ]))->load(__DIR__.'/../fixtures/nested/nested.css');

        $renderer = new Renderer([

            'sourcemap' => true,
            'remove_empty_nodes' => true,
            'legacy_rendering' => true
        ]);

        $outFile = __DIR__.'/../fixtures/sourcemap/generated/nested.test.css';
        $renderer->save($parser, $outFile);

        $data[] = ['/* this row */
table.colortable {
 /* clean all */
 width: 100%;
 text-shadow: none;
 border-collapse /* collapse */: collapse /* collapsed */
}
table.colortable td {
 text-align: center
}
table.colortable td.c {
 text-transform: uppercase;
 background: #ff0
}
table.colortable th {
 text-align: center;
 color: green;
 font-weight: 400;
 padding: 2px 3px
}
table.colortable td,
table.colortable th {
 border: 1px solid #d9dadd;
 padding: 5px
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
:is(.error, #404):hover>.baz {
 color: red
}
',
            preg_replace('#'.preg_quote('/*# sourceMappingURL=', '#').'.*?\*/#', '', file_get_contents($outFile))
        ];

        $data[] = [';AACA;;;;;;AAOI;;;AAEE;;;;AAKF;;;;;;AAME;;;;;AASN;;;AAEA;;;AAEA;;;;AAIA', json_decode(file_get_contents($outFile.'.map'), true)['mappings']];
        $outFile = __DIR__.'/../fixtures/sourcemap/generated/nested.test.min.css';
        $renderer->setOptions([
            'compress' => true
        ])->save($parser, $outFile);

        $data[] = [
            'table.colortable{width:100%;text-shadow:none;border-collapse:collapse}table.colortable td{text-align:center}table.colortable td.c{text-transform:uppercase;background:#ff0}table.colortable th{text-align:center;color:green;font-weight:400;padding:2px 3px}table.colortable td,table.colortable th{border:1px solid #d9dadd;padding:5px}.foo{padding:2ch}.foo{color:blue}.foo.foo{padding:2ch}:is(.error,#404):hover>.baz{color:red}
',
            preg_replace('#'.preg_quote('/*# sourceMappingURL=', '#').'.*?\*/#', '', file_get_contents($outFile))
        ];

        $data[] = ['AACA,sEAOI,sCAEE,+DAKF,kFAME,6EASN,iBAEA,gBAEA,qBAIA', json_decode(file_get_contents($outFile.'.map'), true)['mappings']];

        return $data;
    }
}

