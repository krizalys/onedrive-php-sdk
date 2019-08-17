<?php

namespace Test\Unit\Krizalys\Onedrive\Parameter\Definition;

use Krizalys\Onedrive\Serializer\SerializerInterface;
use Krizalys\Onedrive\Parameter\Definition\HeaderParameterDefinition;

class HeaderParameterDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testSerializeKey()
    {
        $serializer = $this->createMock(SerializerInterface::class);

        $sut    = new HeaderParameterDefinition('name', $serializer);
        $actual = $sut->serializeKey();
        $this->assertSame('name', $actual);
    }
}
