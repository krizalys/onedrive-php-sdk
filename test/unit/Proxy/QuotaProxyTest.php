<?php

declare(strict_types=1);

namespace Test\Unit\Krizalys\Onedrive\Proxy;

use Krizalys\Onedrive\Proxy\QuotaProxy;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Quota;
use PHPUnit\Framework\TestCase;

class QuotaProxyTest extends TestCase
{
    public function testDeletedShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $quota = $this->createMock(Quota::class);

        $quota
            ->expects($this->atLeastOnce())
            ->method('getDeleted')
            ->willReturn(1234);

        $sut = new QuotaProxy($graph, $quota);
        $this->assertIsInt($sut->deleted);
        $this->assertSame(1234, $sut->deleted);
    }

    public function testRemainingShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $quota = $this->createMock(Quota::class);

        $quota
            ->expects($this->atLeastOnce())
            ->method('getRemaining')
            ->willReturn(1234);

        $sut = new QuotaProxy($graph, $quota);
        $this->assertIsInt($sut->remaining);
        $this->assertSame(1234, $sut->remaining);
    }

    public function testStateShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $quota = $this->createMock(Quota::class);

        $quota
            ->expects($this->atLeastOnce())
            ->method('getState')
            ->willReturn(1234);

        $sut = new QuotaProxy($graph, $quota);
        $this->assertIsInt($sut->state);
        $this->assertSame(1234, $sut->state);
    }

    public function testTotalShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $quota = $this->createMock(Quota::class);

        $quota
            ->expects($this->atLeastOnce())
            ->method('getTotal')
            ->willReturn(1234);

        $sut = new QuotaProxy($graph, $quota);
        $this->assertIsInt($sut->total);
        $this->assertSame(1234, $sut->total);
    }

    public function testUsedShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $quota = $this->createMock(Quota::class);

        $quota
            ->expects($this->atLeastOnce())
            ->method('getUsed')
            ->willReturn(1234);

        $sut = new QuotaProxy($graph, $quota);
        $this->assertIsInt($sut->used);
        $this->assertSame(1234, $sut->used);
    }
}
