<?php

namespace Test\Unit\Krizalys\Onedrive\Parameter\Definition;

use Krizalys\Onedrive\Serializer\SerializerInterface;
use Krizalys\Onedrive\Parameter\Definition\AbstractParameterDefinition;

class AbstractParameterDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testSerializeValueShouldInteractWithItsSerializerAsExpected()
    {
        $serializer = $this->createMock(SerializerInterface::class);

        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->with('Value')
            ->willReturn('serialized_value');

        $sut = new TestAbstractParameterDefinition('name', $serializer);

        $actual = $sut->serializeValue('Value');
        $this->assertSame('serialized_value', $actual);
    }
}

class TestAbstractParameterDefinition extends AbstractParameterDefinition
{
    public function serializeKey()
    {
        return '';
    }
}
