<?php

namespace Test\Unit\Krizalys\Onedrive\Parameter;

use Krizalys\Onedrive\Parameter\Definition\ParameterDefinitionInterface;
use Krizalys\Onedrive\Parameter\ParameterBuilder;

class ParameterBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildShouldReturnExpectedValue()
    {
        $parameterDefinition1 = $this->createMock(ParameterDefinitionInterface::class);
        $parameterDefinition1->method('serializeKey')->willReturn('A');
        $parameterDefinition1->method('serializeValue')->willReturnArgument(0);

        $parameterDefinition2 = $this->createMock(ParameterDefinitionInterface::class);
        $parameterDefinition2->method('serializeKey')->willReturn('B');
        $parameterDefinition2->method('serializeValue')->willReturnArgument(0);

        $parameterDefinition3 = $this->createMock(ParameterDefinitionInterface::class);
        $parameterDefinition3->method('serializeKey')->willReturn('C');
        $parameterDefinition3->method('serializeValue')->willReturnArgument(0);

        $sut = new ParameterBuilder();

        $sut->setParameterDefinitions([
            'a' => $parameterDefinition1,
            'b' => $parameterDefinition2,
            'c' => $parameterDefinition3,
        ]);

        $sut->setOptions([
            'a' => 1,
            'b' => 2,
            'd' => 4,
        ]);

        $actual = $sut->build();

        $this->assertSame([
            'A' => 1,
            'B' => 2,
        ], $actual);
    }
}
