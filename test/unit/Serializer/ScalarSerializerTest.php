<?php

namespace Test\Unit\Krizalys\Onedrive\Serializer;

use Krizalys\Onedrive\Serializer\ScalarSerializer;

class ScalarSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider valueProvider
     */
    public function testSerializeShouldReturnExpectedValue($value, $expected)
    {
        $sut = new ScalarSerializer();

        $actual = $sut->serialize($value);
        $this->assertSame($expected, $actual);
    }

    public function valueProvider()
    {
        return [
            ['Test', 'Test'],
            [1234,   '1234'],
        ];
    }
}
