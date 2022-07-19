<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Cli\Args;


final class Cli extends TestCase
{
    /**
     * @param array $expected
     * @param array $actual
     * @dataProvider shortFlagsProvider
     */
    public function testShortFlags(array $expected, array $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param array $expected
     * @param array $actual
     * @dataProvider longFlagsProvider
     */
    public function testLongtFlags(array $expected, array $actual): void
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @param Args $cli
     * @param string $actual
     * @return void
     * @dataProvider exceptionsProvider
     * @throws \TBela\CSS\Cli\Exceptions\MissingParameterException
     */
    public function testExceptionsProvider(Args $cli, string $actual): void
    {

        $this->expectException($actual);
        $cli->parse();
    }
    public function shortFlagsProvider()
    {

        return [
            [

                (new Args([__FILE__, '-a', '1']))->setStrict(false)->parse()->getArguments(),
                ["a" => 1, '_' => []]
            ],
            [

                (new Args([__FILE__, '-a=1']))->setStrict(false)->parse()->getArguments(),
                ["a" => 1, '_' => []]
            ],
            [

                (new Args([__FILE__, '-a1']))->setStrict(false)->parse()->getArguments(),
                ["a" => 1, '_' => []]
            ],
            [

                (new Args([__FILE__, '-uroot']))->setStrict(false)->
                add('user', 'username', 'string', ['u'])->parse()->getArguments(),
                ["user" => 'root', '_' => []]
            ],
            [

                (new Args([__FILE__, '-a', '1']))->setStrict(false)->
                    add('a', 'param a', 'bool')->parse()->getArguments(),
                ["a" => true, '_' => ['1']]
                ],
            [

            (new Args([__FILE__, '-auroot']))->setStrict(false)->
            add('a', 'param a', 'bool', 'a')->
            add('u', 'param u', 'string', 'u')->
            parse()->getArguments(),
                ["u" => "root", "a" => true, '_' => []]
            ]
        ];
    }

    public function longFlagsProvider()
    {

        return [
            [

                (new Args([__FILE__, '--a', '1']))->setStrict(false)->parse()->getArguments(),
                ["a" => 1, '_' => []]
            ],
            [

                (new Args([__FILE__, '--a=1']))->setStrict(false)->parse()->getArguments(),
                ["a" => 1, '_' => []]
            ],
            [

                (new Args([__FILE__, '--a1']))->setStrict(false)->parse()->getArguments(),
                ["a1" => true, '_' => []]
            ],
            [

                (new Args([__FILE__, '--a', '1']))->setStrict(false)->
                add('a', 'param a', 'bool')->parse()->getArguments(),
                ["a" => true, '_' => ['1']]
            ]
        ];
    }

    public function exceptionsProvider()
    {

        return [
            [

                // require parameter
                (new Args([__FILE__]))->add('req', 'required parameter', 'auto', required:true)->
                setStrict(false),
                \TBela\CSS\Cli\Exceptions\MissingParameterException::class
            ],
            [

            (new Args([__FILE__, '-r1']))->add('req', 'required parameter', 'auto', alias: 'r', required:true, options: [2, 5])->
            setStrict(false),
                \ValueError::class
            ],
            [

                (new Args([__FILE__, '--req=1']))->add('req', 'required parameter', 'auto', alias: 'r', required:true, options: [2, 5])->
                setStrict(false),
                \ValueError::class
            ],
            [

                (new Args([__FILE__, '--req=c']))->add('req', 'required parameter', 'int', alias: 'r', required:true, options: [2, 5])->
                setStrict(false),
                \UnexpectedValueException::class
            ]
        ];
    }
}

