<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Element\Declaration\PropertyList;

require_once __DIR__.'/../bootstrap.php';

final class PropertiesTest extends TestCase
{
    /**
     * @param PropertyList $propertylist
     * @param $name
     * @param $value
     * @param $expected
     * @return void
     * @dataProvider PropertyComposeProvider
     */
    public function testPropertyCompose(PropertyList $propertylist, $name, $value, $expected)
    {

        $propertylist->set($name, $value);
        $this->assertEquals($expected, (string)$propertylist);
    }

    /**
     * @param PropertyList $property
     * @param $name
     * @param $value
     * @param $expected
     * @dataProvider propertySetProvider
     */
    public function testPropertySet(PropertyList $property, $name, $value, $expected)
    {

        $property->set($name, $value);

        $this->assertEquals(
            $expected,
            (string)$property
        );
    }

    /**
     * @param PropertyList $property
     * @param $name
     * @param $value
     * @param $expected
     * @dataProvider propertyHackProvider
     */
    public function testPropertyHack(PropertyList $property, $name, $value, $expected)
    {

        $property->set($name, $value);

        $this->assertEquals(
            $expected,
            (string)$property
        );
    }

    public function propertyComposeProvider()
    {

        $propertylist = new PropertyList();
        return [
            [$propertylist, 'border-top-left-radius', '1em 5em', 'border-top-left-radius: 1em 5em'],
            [$propertylist, 'border-top-right-radius', '1em 5em', 'border-top-left-radius: 1em 5em;' . "\n" .
                'border-top-right-radius: 1em 5em'],
            [$propertylist, 'border-bottom-left-radius', '1em 5em', 'border-top-left-radius: 1em 5em;' . "\n" .
                'border-top-right-radius: 1em 5em;' . "\n" .
                'border-bottom-left-radius: 1em 5em'],
            [$propertylist, 'border-bottom-right-radius', '1em 4em', 'border-radius: 1em/5em 5em 4em'],
            [$propertylist, 'border-bottom-right-radius', '1em 5em', 'border-radius: 1em/5em']
        ];
    }

    public function propertySetProvider()
    {

        $property = new PropertyList();

        $data[] = [$property, 'border-radius', '10% 17% 10% 17% / 50% 20% 50% 20%', 'border-radius: 10% 17%/50% 20%'];
        $data[] = [$property, 'border-top-left-radius', '1em 5em', 'border-radius: 1em 17% 10%/5em 20% 50%'];
        $data[] = [$property, 'border-top-right-radius', '1em 5em', 'border-radius: 1em 1em 10% 17%/5em 5em 50% 20%'];
        $data[] = [$property, 'border-bottom-left-radius', '1em 5em', 'border-radius: 1em 1em 10%/5em 5em 50%'];
        $data[] = [$property, 'border-bottom-right-radius', '1em 5em', 'border-radius: 1em/5em'];

        $property2 = new PropertyList();

        $data[] = [$property2, '-moz-border-radius', '10% 17% 10% 17% / 50% 20% 50% 20%', '-moz-border-radius: 10% 17%/50% 20%'];
        $data[] = [$property2, '-moz-border-radius-topleft', '1em 5em', '-moz-border-radius: 1em 17% 10%/5em 20% 50%'];
        $data[] = [$property2, '-moz-border-radius-topright', '1em 5em', '-moz-border-radius: 1em 1em 10% 17%/5em 5em 50% 20%'];
        $data[] = [$property2, '-moz-border-radius-bottomleft', '1em 5em', '-moz-border-radius: 1em 1em 10%/5em 5em 50%'];
        $data[] = [$property2, '-moz-border-radius-bottomright', '1em 5em', '-moz-border-radius: 1em/5em'];

        $property3 = new PropertyList();

        $data[] = [$property3, '-webkit-border-radius', '10% 17% 10% 17% / 50% 20% 50% 20%', '-webkit-border-radius: 10% 17%/50% 20%'];
        $data[] = [$property3, '-webkit-border-top-left-radius', '1em 5em', '-webkit-border-radius: 1em 17% 10%/5em 20% 50%'];
        $data[] = [$property3, '-webkit-border-top-right-radius', '1em 5em', '-webkit-border-radius: 1em 1em 10% 17%/5em 5em 50% 20%'];
        $data[] = [$property3, '-webkit-border-bottom-left-radius', '1em 5em', '-webkit-border-radius: 1em 1em 10%/5em 5em 50%'];
        $data[] = [$property3, '-webkit-border-bottom-right-radius', '1em 5em', '-webkit-border-radius: 1em/5em'];

        $property1 = new PropertyList();

        $data[] = [$property1, 'margin', '5px 3px', 'margin: 5px 3px'];
        $data[] = [$property1, 'margin', '0 0 15px 15px', 'margin: 0 0 15px 15px'];
        $data[] = [$property1, 'margin-left', '15px', 'margin: 0 0 15px 15px'];
        $data[] = [$property1, 'margin-top', '15px', 'margin: 15px 0 15px 15px'];
        $data[] = [$property1, 'margin-top', '0px', 'margin: 0 0 15px 15px'];
        $data[] = [$property1, 'margin-left', '0px', 'margin: 0 0 15px'];
        $data[] = [$property1, 'margin-top', '15px', 'margin: 15px 0'];
        $data[] = [$property1, 'margin-left', '0', 'margin: 15px 0'];
        $data[] = [$property1, 'margin', '0 auto', 'margin: 0 auto'];

		$property4 = new PropertyList();

		$data[] = [$property4, 'margin-top', '5px \\9', 'margin-top: 5px \\9'];
		$data[] = [$property4, 'margin-left', '5px \\9', "margin-top: 5px \\9;\nmargin-left: 5px \\9"];
		$data[] = [$property4, 'margin-bottom', '5px \\9', "margin-top: 5px \\9;\nmargin-left: 5px \\9;\nmargin-bottom: 5px \\9"];
		$data[] = [$property4, 'margin-right', '5px \\9', 'margin: 5px \\9'];

		$property5 = new PropertyList();

		$data[] = [$property5, 'text-decoration-line', 'line-through ', 'text-decoration-line: line-through'];
		$data[] = [$property5, 'text-decoration-color', 'red ', "text-decoration-line: line-through;\ntext-decoration-color: red"];
		$data[] = [$property5, 'text-decoration-style', ' double', "text-decoration-line: line-through;\ntext-decoration-color: red;\ntext-decoration-style: double"];
		$data[] = [$property5, 'text-decoration-thickness', ' 3px ', "text-decoration: line-through red double 3px"];

        return $data;
    }

    public function propertyHackProvider()
    {

        $property = new PropertyList();

        $data[] = [$property, 'margin-top', '1px \\9', 'margin-top: 1px \\9'];
        $data[] = [$property, 'margin-right', '1px \\9', 'margin-top: 1px \\9;'."\n".'margin-right: 1px \\9'];
        $data[] = [$property, 'margin-bottom', '1px \\9', 'margin-top: 1px \\9;'."\n".'margin-right: 1px \\9;'."\n".'margin-bottom: 1px \\9'];
        $data[] = [$property, 'margin-left', '1px \\9', 'margin: 1px \\9'];
        return $data;
    }
}

