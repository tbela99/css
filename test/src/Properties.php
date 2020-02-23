<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Property\PropertyList;

// because git changes \n to \r\n at some point, this causes test failure
function get_content($file)
{

    return str_replace("\r\n", "\n", file_get_contents($file));
}


final class Properties extends TestCase
{

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
            (string)$property,
            $expected
        );
    }

    public function propertySetProvider()
    {

        $data = [];

        $property = new PropertyList();

        $data[] = [$property, 'border-radius', '10% 17% 10% 17% / 50% 20% 50% 20%', 'border-radius: 10% 17%/50% 20%'];
        $data[] = [$property, 'border-top-left-radius', '1em 5em', 'border-radius: 1em 17% 10%/5em 20% 50%'];
        $data[] = [$property, 'border-top-right-radius', '1em 5em', 'border-radius: 1em 1em 10% 17%/5em 5em 50% 20%'];
        $data[] = [$property, 'border-bottom-left-radius', '1em 5em', 'border-radius: 1em 1em 10%/5em 5em 50%'];
        $data[] = [$property, 'border-bottom-right-radius', '1em 5em', 'border-radius: 1em/5em'];

        $property1 = new PropertyList();

        $data[] = [$property1, 'margin', '0 0 15px 15px', 'margin: 0 0 15px 15px'];
        $data[] = [$property1, 'margin-left', '15px', 'margin: 0 0 15px 15px'];
        $data[] = [$property1, 'margin-top', '15px', 'margin: 15px 0 15px 15px'];
        $data[] = [$property1, 'margin-top', '0px', 'margin: 0 0 15px 15px'];
        $data[] = [$property1, 'margin-left', '0px', 'margin: 0 0 15px'];
        $data[] = [$property1, 'margin-top', '15px', 'margin: 15px 0'];
        $data[] = [$property1, 'margin-left', '0', 'margin: 15px 0'];

        return $data;
    }
}

