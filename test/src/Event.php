<?php

use PHPUnit\Framework\TestCase;
use TBela\CSS\Event\Event as EventTest;

final class Event extends TestCase
{
    /**
     * @param array $expected
     * @param array $actual
     * @dataProvider testEventProvider
     */
    public function testEvent(array $expected, array $actual)
    {

        $this->assertEquals(
            $expected,
          $actual
        );
    }

/*
*/
    public function testEventProvider () {

        $emitter = new EventTest();

        $data = [];

        $double = function ($x) {

            return 2 * $x;
        };

        $emitter->on('compute', $double);

        $data[] = [

            [2],
            $emitter->emit('compute', 1)
        ];

        $data[] = [

            [6],
            $emitter->emit('compute', 3)
        ];

        $emitter->off('compute', $double);

        $data[] = [

            [],
            $emitter->emit('compute', 1)
        ];

        return $data;
    }
}

