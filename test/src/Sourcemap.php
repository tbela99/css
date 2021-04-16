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
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider testSourcemapImportProvider
     */
    public function testSourcemapImport($expected, $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    public function testSourcemapProvider() {

        $data = [];

        $element = (new Parser())->load(__DIR__ . '/../sourcemap/sourcemap.css')->
        append(__DIR__ . '/../sourcemap/sourcemap.2.css')->
        append(__DIR__ . '/../sourcemap/sourcemap.media.css')->parse();

        $renderer = new Renderer([
            'sourcemap' => true
        ]);

        $outFile = __DIR__.'/../sourcemap/generated/sourcemap.generated.test.css';
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
 osx-font-smoothing: grayscale;
 font-smoothing: antialiased;
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

        $data[] = ['{"version":3,"file":"","sourceRoot":"","sources":["\/home\/test\/PhpstormProjects\/css\/test\/sourcemap\/sourcemap.css","\/home\/test\/PhpstormProjects\/css\/test\/sourcemap\/sourcemap.2.css","\/home\/test\/PhpstormProjects\/css\/test\/sourcemap\/sourcemap.media.css"],"names":[],"mappings":"AACA;;;;;;;;;AAaA;;;;;;;;;;;;;;;AAgBA;;;AC7BA;;;AAIA;;;;ACFI"}', file_get_contents($outFile.'.map')];

        $outFile = __DIR__.'/../sourcemap/generated/sourcemap.generated.test.min.css';

        $renderer->setOptions([
            'compress' => true
        ])->save($element, $outFile);

        $data[] = ['{"version":3,"file":"","sourceRoot":"","sources":["\/home\/test\/PhpstormProjects\/css\/test\/sourcemap\/sourcemap.css","\/home\/test\/PhpstormProjects\/css\/test\/sourcemap\/sourcemap.2.css","\/home\/test\/PhpstormProjects\/css\/test\/sourcemap\/sourcemap.media.css"],"names":[],"mappings":"AACA,6DAaA,kLAgBA,6DC7BA,+BAIA,8CCFI"}', file_get_contents($outFile.'.map')];

        return $data;
    }

    public function testSourcemapImportProvider() {

        $data = [];

        $element = (new Parser('', [
            'flatten_import' => true
        ]))->load(__DIR__ . '/../sourcemap/sourcemap.import.css')->parse();

        $renderer = new Renderer([
            'sourcemap' => true
        ]);

        $outFile = __DIR__.'/../sourcemap/generated/sourcemap.generated.import.test.css';
        $renderer->save($element, $outFile);

        $data[] = ['/*! this is supposed to be the license. */
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
 /* don\'t comment */
 osx-font-smoothing: grayscale;
 font-smoothing: antialiased;
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
 content: "s-2 ";
 font-size: 80%
}
/* import the media library */
body {
 /*font-size: 14px*/
 line-height: 1.3
}
',
            preg_replace('#'.preg_quote('/*# sourceMappingURL=', '#').'.*?\*/#', '', file_get_contents($outFile))
        ];

        $data[] = ['{"version":3,"file":"","sourceRoot":"","sources":["\/home\/test\/PhpstormProjects\/css\/test\/sourcemap\/sourcemap.css","\/home\/test\/PhpstormProjects\/css\/test\/sourcemap\/sourcemap.2.css","\/home\/test\/PhpstormProjects\/css\/test\/sourcemap\/sourcemap.media.css"],"names":[],"mappings":";AACA;;;;;;;;;AAaA;;;;;;;;;;;;;;;AAgBA;;;;AC7BA;;;AAIA;;;;;ACFI"}', file_get_contents($outFile.'.map')];

        $outFile = __DIR__.'/../sourcemap/generated/sourcemap.generated.import.test.min.css';

        $renderer->setOptions([
            'compress' => true
        ])->save($element, $outFile);

        $data[] = ['{"version":3,"file":"","sourceRoot":"","sources":["\/home\/test\/PhpstormProjects\/css\/test\/sourcemap\/sourcemap.css","\/home\/test\/PhpstormProjects\/css\/test\/sourcemap\/sourcemap.2.css","\/home\/test\/PhpstormProjects\/css\/test\/sourcemap\/sourcemap.media.css"],"names":[],"mappings":"AACA,6DAaA,kLAgBA,6DC7BA,+BAIA,8CCFI"}', file_get_contents($outFile.'.map')];

        return $data;
    }
}

