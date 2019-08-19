<?php

namespace Test\Unit\Krizalys\Onedrive\Parameter\Injector;

use Krizalys\Onedrive\Parameter\Injector\FlatInjector;

class FlatInjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideInjectReturnExpectedValueTestCases
     */
    public function testInjectReturnExpectedValue(array $values, array $expected)
    {
        $sut    = new FlatInjector('key');
        $actual = $sut->inject($values, 'Value');
        $this->assertSame($expected, $actual);
    }

    public function provideInjectReturnExpectedValueTestCases()
    {
        return [
            [
                [],
                ['key' => 'Value'],
            ],
            [
                ['existing_key' => 'Existing value'],
                [
                    'existing_key' => 'Existing value',
                    'key'          => 'Value',
                ],
            ],
        ];
    }
}
