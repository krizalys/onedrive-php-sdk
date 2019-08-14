<?php

declare(strict_types=1);

namespace Test\Unit\Krizalys\Onedrive\Parameter\Injector;

use Krizalys\Onedrive\Parameter\Injector\HierarchicalInjector;
use PHPUnit\Framework\TestCase;

class HierarchicalInjectorTest extends TestCase
{
    /**
     * @dataProvider provideInjectReturnExpectedValueTestCases
     */
    public function testInjectReturnExpectedValue($path, $values, $expected)
    {
        $sut    = new HierarchicalInjector($path);
        $actual = $sut->inject($values, 'Value');
        $this->assertSame($expected, $actual);
    }

    public function testInjectWithEmptyPathThrowsExpectedException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('A hierarchical injector path cannot be empty');

        $sut = new HierarchicalInjector([]);
        $sut->inject([], 'Irrelevant');
    }

    public function provideInjectReturnExpectedValueTestCases()
    {
        return [
            [
                ['key'],
                [],
                ['key' => 'Value'],
            ],
            [
                ['key'],
                ['existing_key' => 'Existing value'],
                [
                    'existing_key' => 'Existing value',
                    'key'          => 'Value',
                ],
            ],
            [
                ['key'],
                ['key' => 'Existing value'],
                ['key' => 'Value'],
            ],
            [
                ['path', 'to'],
                [],
                [
                    'path' => ['to' => 'Value'],
                ],
            ],
            [
                ['path', 'to'],
                [
                    'existing_key_1' => 'Existing value #1',
                    'path'           => ['existing_key_2' => 'Existing value #2'],
                ],
                [
                    'existing_key_1' => 'Existing value #1',
                    'path' => [
                        'existing_key_2' => 'Existing value #2',
                        'to'             => 'Value',
                    ],
                ],
            ],
        ];
    }
}
