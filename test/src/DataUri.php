<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Element;
use TBela\CSS\Element\AtRule;
use TBela\CSS\Element\Stylesheet;
use TBela\CSS\Compiler;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

final class DataUri extends TestCase
{
    /**
     * @param string $parsed
     * @param string $expected
     * @dataProvider DataUriProvider
     */
    public function testDataUri($parsed, $expected)
    {
        $this->assertEquals(
            $expected,
            $parsed
        );
    }

    public function DataUriProvider () {

        $parser = new TBela\CSS\Parser;

        $parser->load(__DIR__.'/../data/uri.css');

        $data = [];

        $data[] = [(string) $parser->parse(),
            '.test {
 background-color: red
}
.t228__white-black .ya-share2__container_size_m .ya-share2__item_service_facebook
.ya-share2__icon {
 background-image: url(data:image/svg+xml;base64,PHN2ZyBmaWxsPSIjMjIyIiB2aWV3Qm94PSIwIDAgMjggMjgiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTE1LjEgMjN2LTguMjFoMi43NzNsLjQxNS0zLjJIMTUuMVY5LjU0N2MwLS45MjcuMjYtMS41NTggMS41OTYtMS41NThsMS43MDQtLjAwMlY1LjEyNkEyMi43ODcgMjIuNzg3IDAgMCAwIDE1LjkxNyA1QzEzLjQ2IDUgMTEuNzggNi40OTIgMTEuNzggOS4yM3YyLjM2SDl2My4yaDIuNzhWMjNoMy4zMnoiIGZpbGwtcnVsZT0iZXZlbm9kZCIvPjwvc3ZnPg==)
}
.test {
 color: red
}'];

        return $data;
    }
}

