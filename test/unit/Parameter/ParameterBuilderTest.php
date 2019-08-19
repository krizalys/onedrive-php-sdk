<?php

namespace Test\Unit\Krizalys\Onedrive\Parameter;

use Krizalys\Onedrive\Parameter\Definition\ParameterDefinitionInterface;
use Krizalys\Onedrive\Parameter\ParameterBuilder;

class ParameterBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildShouldReturnExpectedValue()
    {
        $parameterDefinition1 = $this->createMock(ParameterDefinitionInterface::class);

        $parameterDefinition1->method('serializeValue')->willReturnCallback(function ($value) {
            return "Serialized $value";
        });

        $parameterDefinition1->method('injectValue')->willReturnCallback(function (array $values, $value) {
            return $values + [$value => $value];
        });

        $parameterDefinition2 = $this->createMock(ParameterDefinitionInterface::class);

        $parameterDefinition2->method('serializeValue')->willReturnCallback(function ($value) {
            return "Serialized $value";
        });

        $parameterDefinition2->method('injectValue')->willReturnCallback(function (array $values, $value) {
            return $values + [$value => $value];
        });

        $sut = new ParameterBuilder();

        $sut->setParameterDefinitions([
            '1' => $parameterDefinition1,
            '2' => $parameterDefinition2,
            '3' => $parameterDefinition2,
        ]);

        $sut->setOptions([
            '1' => 1,
            '2' => 2,
            '4' => 4,
        ]);

        $actual = $sut->build();

        $this->assertSame([
            'Serialized 1' => 'Serialized 1',
            'Serialized 2' => 'Serialized 2',
        ], $actual);
    }
}
