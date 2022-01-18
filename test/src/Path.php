<?php

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

final class Path extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider testRelativeProvider
     */
    public function testRelative($expected, $actual)
    {

        $this->assertEquals(
            $expected,
          $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider testAbsoluteProvider
     */
    public function testAbsolute($expected, $actual)
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider testHttpPathProvider
     */
    public function testHttpPath($expected, $actual)
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    public function testRelativeProvider() {

        $data = [];

        $data[] = [

            '../images/icon.png?#iefix',
            Parser\Helper::relativePath('http://example.com/images/icon.png?#iefix',  'http://example.com/css/')
        ];

        $data[] = [

            '../images/icon.png?#iefix',
            Parser\Helper::relativePath('http://example.com/images/icon.png?#iefix',  'http://example.com/css')
        ];

        $data[] = [

            '../template/images/icon.png?#iefix',
            Parser\Helper::relativePath('http://example.com/template/images/icon.png?#iefix',  'http://example.com/css/')
        ];

        $data[] = [

            '../template/images/icon.png?#iefix',
            Parser\Helper::relativePath('http://example.com/template/images/icon.png?#iefix',  'http://example.com/css')
        ];

        $data[] = [

            'images/icon.png?#iefix',
            Parser\Helper::relativePath('http://example.com/css/images/icon.png?#iefix',  'http://example.com/css/')
        ];

        $data[] = [

            'images/icon.png?#iefix',
            Parser\Helper::relativePath('http://example.com/css/images/icon.png?#iefix',  'http://example.com/css')
        ];

        $data[] = [

            'icon.png?#iefix',
            Parser\Helper::relativePath('http://example.com/css/icon.png?#iefix',  'http://example.com/css/')
        ];

        $data[] = [

            'icon.png?#iefix',
            Parser\Helper::relativePath('http://example.com/css/icon.png?#iefix',  'http://example.com/css')
        ];

        $data[] = [

            '/CenturyGothic.woff',
            Parser\Helper::relativePath('/CenturyGothic.woff',  'http://example.com/css')
        ];

        $data[] = [

            '/CenturyGothic.woff',
            Parser\Helper::relativePath('/CenturyGothic.woff',  '/home/test/projects/css/tests/fonts/')
        ];

        return $data;
    }

    public function testAbsoluteProvider() {

        $data = [];

        $data[] = [

            'http://example.com/images/icon.png',
            Parser\Helper::absolutePath('http://example.com/images/icon.png',  'http://example.com/css/')
        ];

        $data[] = [

            'http://example.com/images/icon.png',
            Parser\Helper::absolutePath('../images/icon.png',  'http://example.com/css/')
        ];

        $data[] = [

            'http://example.com/images/icon.png',
            Parser\Helper::absolutePath('../images/icon.png',  'http://example.com/css')
        ];

        $data[] = [

            '//example.com/images/icon.png',
            Parser\Helper::absolutePath('../images/icon.png',  '//example.com/css')
        ];

        $data[] = [

            '/example.com/images/icon.png',
            Parser\Helper::absolutePath('../images/icon.png',  '/example.com/css')
        ];

        $data[] = [

            'example.com/images/icon.png',
            Parser\Helper::absolutePath('../images/icon.png',  'example.com/css')
        ];

        $data[] = [

            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/webfonts/fa-brands-400.eot?#iefix',
            Parser\Helper::absolutePath('../webfonts/fa-brands-400.eot?#iefix',  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/')
        ];

        return $data;
    }

    public function testHttpPathProvider() {

        $data = [];
        $port = '9992';
        $kill_server = sprintf('ps -ux | grep %s | xargs k -9 >/dev/null 2>&1', $port);

        var_dump(sprintf("%s; cd %s && php5.6 -S %s:%s -t . %s > /dev/null 2>&1 &", $kill_server, escapeshellarg(__DIR__.'/..'), '127.0.0.1', $port, 'server.php'));
        shell_exec(sprintf("%s; cd %s && php5.6 -S %s:%s -t . %s > /dev/null 2>&1 &", $kill_server, escapeshellarg(__DIR__.'/..'), '127.0.0.1', $port, 'server.php'));

        $data[] = [

            file_get_contents(__DIR__.'/../sourcemap/sourcemap.http.css'),
            Parser\Helper::fetchContent('http://127.0.0.1:'.$port.'/sourcemap/sourcemap.import.css')
        ];

        shell_exec($kill_server);

        return $data;
    }
}

