<?php

namespace Test\Krizalys\Onedrive;

use Krizalys\Onedrive\NameConflictBehavior;
use Krizalys\Onedrive\NameConflictBehaviorParameterizer;

class NameConflictBehaviorParameterizerTest extends \PHPUnit_Framework_TestCase
{
    public function provideParameterizeShouldReturnExpectedValue()
    {
        return array(
            'FAIL' => array(
                'params'               => array('key' => 'value'),
                'nameConflictBehavior' => NameConflictBehavior::FAIL,
                'expected'             => array('key' => 'value', 'overwrite' => 'false'),
            ),

            'RENAME' => array(
                'params'               => array('key' => 'value'),
                'nameConflictBehavior' => NameConflictBehavior::RENAME,
                'expected'             => array('key' => 'value', 'overwrite' => 'ChooseNewName'),
            ),

            'REPLACE' => array(
                'params'               => array('key' => 'value'),
                'nameConflictBehavior' => NameConflictBehavior::REPLACE,
                'expected'             => array('key' => 'value', 'overwrite' => 'true'),
            ),
        );
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
        $actual        = $parameterizer->parameterize(array(), 0);
    }
}
