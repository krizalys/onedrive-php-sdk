<?php

namespace Test\Unit\Krizalys\Onedrive\Proxy;

use Krizalys\Onedrive\Proxy\ItemReferenceProxy;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\ItemReference;

class ItemReferenceProxyTest extends \PHPUnit_Framework_TestCase
{
    public function testIdShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $itemReference = $this->createMock(ItemReference::class);
        $itemReference->method('getId')->willReturn('1234');

        $sut = new ItemReferenceProxy($graph, $itemReference);
        $this->assertInternalType('string', $sut->id);
        $this->assertSame('1234', $sut->id);
    }

    public function testDriveIdShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $itemReference = $this->createMock(ItemReference::class);
        $itemReference->method('getDriveId')->willReturn('1234');

        $sut = new ItemReferenceProxy($graph, $itemReference);
        $this->assertInternalType('string', $sut->driveId);
        $this->assertSame('1234', $sut->driveId);
    }

    public function testDriveTypeShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $itemReference = $this->createMock(ItemReference::class);
        $itemReference->method('getDriveType')->willReturn('personal');

        $sut = new ItemReferenceProxy($graph, $itemReference);
        $this->assertInternalType('string', $sut->driveType);
        $this->assertSame('personal', $sut->driveType);
    }

    public function testPathShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $itemReference = $this->createMock(ItemReference::class);
        $itemReference->method('getPath')->willReturn('/path');

        $sut = new ItemReferenceProxy($graph, $itemReference);
        $this->assertInternalType('string', $sut->path);
        $this->assertSame('/path', $sut->path);
    }
}
