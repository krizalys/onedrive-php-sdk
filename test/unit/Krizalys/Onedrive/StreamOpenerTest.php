<?php

namespace Test\Krizalys\Onedrive;

use Krizalys\Onedrive\StreamBackEnd;
use Krizalys\Onedrive\StreamOpener;
use Test\Mock\GlobalNamespace;

class StreamOpenerTest extends \PHPUnit_Framework_TestCase
{
    public function provideOpenShouldCallOnceFopenWithExpectedArguments()
    {
        return array(
            'MEMORY' => array(
                'streamBackEnd' => StreamBackEnd::MEMORY,
                'expected'      => array('php://memory', 'rw+b', false, null),
            ),

            'TEMP' => array(
                'streamBackEnd' => StreamBackEnd::TEMP,
                'expected'      => array('php://temp', 'rw+b', false, null),
            ),
        );
    }

    /**
     * @dataProvider provideOpenShouldCallOnceFopenWithExpectedArguments
     */
    public function testOpenShouldCallOnceFopenWithExpectedArguments(
        $streamBackEnd,
        $expected
    ) {
        GlobalNamespace::reset(array(
            'fopen' => function ($expectation) use ($expected) {
                $expectation
                    ->once()
                    ->withArgs($expected);
            },
        ));

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
