<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;


final class Selector extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider selectorRemoveProvider
     */
    public function testSelectorRemove(string $expected, string $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    public function selectorRemoveProvider()
    {


        $parser = new Parser('.widget-rss.red .title,
.widget-recent .title {
  color: red;
}
aside .widget-rss:hover {
  background: #fff;
}');

        $stylesheet = $parser->parse();


        foreach ($stylesheet->query("[value*='.widget-rss']") as $p) {
            foreach ($p->getSelector() as $selector) {
                if (strpos($selector, '.widget-rss') !== false) {

                    try {

                        $p->removeSelector($selector);
                    } catch (Exception $e) {

                        $p['parentNode']->remove($p);
                    }
                }
            }
        }

        return [
            [

                '.widget-recent .title {
 color: red
}',
                (string)$stylesheet
            ]
        ];

    }
}

