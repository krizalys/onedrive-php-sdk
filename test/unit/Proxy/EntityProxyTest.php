<?php

declare(strict_types=1);

namespace Test\Unit\Krizalys\Onedrive\Proxy;

use Krizalys\Onedrive\Proxy\EntityProxy;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Entity;
use PHPUnit\Framework\TestCase;

class EntityProxyTest extends TestCase
{
    public function testIdShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $entity = $this->createMock(Entity::class);

        $entity
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn('1234');

        $sut = new EntityProxy($graph, $entity);
        $this->assertIsString($sut->id);
        $this->assertSame('1234', $sut->id);
    }
}
