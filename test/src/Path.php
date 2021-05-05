<?php
declare(strict_types=1);

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
    public function testRelative($expected, $actual): void
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
    public function testAbsolute($expected, $actual): void
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
}

