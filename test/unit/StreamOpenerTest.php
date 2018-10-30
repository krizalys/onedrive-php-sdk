<?php

namespace Test\Unit\Krizalys\Onedrive;

use Krizalys\Onedrive\StreamBackEnd;
use Krizalys\Onedrive\StreamOpener;
use Test\Unit\Mock\GlobalNamespace;

class StreamOpenerTest extends \PHPUnit_Framework_TestCase
{
    public function provideOpenShouldCallOnceFopenWithExpectedArguments()
    {
        return [
            'MEMORY' => [
                'streamBackEnd' => StreamBackEnd::MEMORY,
                'expected'      => ['php://memory', 'rw+b', false, null],
            ],

            'TEMP' => [
                'streamBackEnd' => StreamBackEnd::TEMP,
                'expected'      => ['php://temp', 'rw+b', false, null],
            ],
        ];
    }

    /**
     * @dataProvider provideOpenShouldCallOnceFopenWithExpectedArguments
     */
    public function testOpenShouldCallOnceFopenWithExpectedArguments(
        $streamBackEnd,
        $expected
    ) {
        GlobalNamespace::reset([
            'fopen' => function ($expectation) use ($expected) {
                $expectation
                    ->once()
                    ->withArgs($expected);
            },
        ]);

        $opener = new StreamOpener();
        $opener->open($streamBackEnd);
    }

    /**
     * @expectedException \Exception
     */
    public function testOpenWithUnsupportedBackEndShouldThrowException()
    {
        $opener = new StreamOpener();
        $opener->open(0);
    }
}
