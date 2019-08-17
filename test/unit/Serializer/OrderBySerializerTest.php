<?php

namespace Test\Unit\Krizalys\Onedrive\Serializer;

use Krizalys\Onedrive\Serializer\OrderBySerializer;

class OrderBySerializerTest extends \PHPUnit_Framework_TestCase
{
    public function testSerializeShouldReturnExpectedValue()
    {
        $sut = new OrderBySerializer();

        $actual = $sut->serialize([
            ['a', 'asc'],
            ['b', 'desc'],
        ]);

        $this->assertSame('a asc, b desc', $actual);
    }
}
