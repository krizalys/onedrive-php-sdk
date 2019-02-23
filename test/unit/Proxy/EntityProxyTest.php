<?php

namespace Test\Unit\Krizalys\Onedrive\Proxy;

use Krizalys\Onedrive\Proxy\EntityProxy;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Entity;

class EntityProxyTest extends \PHPUnit_Framework_TestCase
{
    public function testIdShouldReturnExpectedValue()
    {
        $graph  = $this->createMock(Graph::class);
        $entity = $this->createMock(Entity::class);
        $entity->method('getId')->willReturn('1234');
        $sut = new EntityProxy($graph, $entity);
        $this->assertInstanceOf(EntityProxy::class, $sut);
        $this->assertSame('1234', $sut->id);
    }
}
