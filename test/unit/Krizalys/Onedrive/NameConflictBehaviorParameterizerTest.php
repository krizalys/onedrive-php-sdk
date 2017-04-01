<?php

namespace Test\Unit\Krizalys\Onedrive;

use Krizalys\Onedrive\NameConflictBehavior;
use Krizalys\Onedrive\NameConflictBehaviorParameterizer;

class NameConflictBehaviorParameterizerTest extends \PHPUnit_Framework_TestCase
{
    public function provideParameterizeShouldReturnExpectedValue()
    {
        return [
            'FAIL' => [
                'params'               => ['key' => 'value'],
                'nameConflictBehavior' => NameConflictBehavior::FAIL,
                'expected'             => ['key' => 'value', 'overwrite' => 'false'],
            ],

            'RENAME' => [
                'params'               => ['key' => 'value'],
                'nameConflictBehavior' => NameConflictBehavior::RENAME,
                'expected'             => ['key' => 'value', 'overwrite' => 'ChooseNewName'],
            ],

            'REPLACE' => [
                'params'               => ['key' => 'value'],
                'nameConflictBehavior' => NameConflictBehavior::REPLACE,
                'expected'             => ['key' => 'value', 'overwrite' => 'true'],
            ],
        ];
    }

    /**
     * @dataProvider provideParameterizeShouldReturnExpectedValue
     */
    public function testParameterizeShouldReturnExpectedValue(
        $params,
        $nameConflictBehavior,
        $expected
    ) {
        $parameterizer = new NameConflictBehaviorParameterizer();
        $actual        = $parameterizer->parameterize($params, $nameConflictBehavior);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Exception
     */
    public function testParameterizeWithUnsupportedBehaviorShouldThrowException()
    {
        $parameterizer = new NameConflictBehaviorParameterizer();
        $actual        = $parameterizer->parameterize([], 0);
    }
}
