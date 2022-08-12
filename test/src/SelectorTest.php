<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;

require_once __DIR__.'/../bootstrap.php';

final class SelectorTest extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider selectorRemoveProvider
	 * @medium
     */
    public function testSelectorRemove(string $expected, string $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

	/**
	 * @param string $expected
	 * @param string $actual
	 * @dataProvider selectorSiblingProvider
	 * @medium
	 */
	public function testSelectorSibling(string $expected, string $actual): void
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

    public function selectorSiblingProvider()
    {


        $parser = new Parser('.cb + .a~.b.cd[type~="ab cd"] {dir:rtl;}
        ');

        return [
            [

                '.cb+.a~.b.cd[type~="ab cd"] {
 dir: rtl
}',
                (string)$parser
            ]
        ];

    }
}

