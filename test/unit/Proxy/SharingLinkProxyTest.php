<?php

namespace Test\Unit\Krizalys\Onedrive\Proxy;

use Krizalys\Onedrive\Proxy\IdentityProxy;
use Krizalys\Onedrive\Proxy\SharingLinkProxy;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Identity;
use Microsoft\Graph\Model\SharingLink;

class SharingLinkProxyTest extends \PHPUnit_Framework_TestCase
{
    public function testApplicationShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $identity = $this->createMock(Identity::class);
        $identity->method('getDisplayName')->willReturn('Display Name');

        $sharingLink = $this->createMock(SharingLink::class);
        $sharingLink->method('getApplication')->willReturn($identity);

        $sut = new SharingLinkProxy($graph, $sharingLink);
        $this->assertInstanceOf(IdentityProxy::class, $sut->application);
        $this->assertSame('Display Name', $sut->application->displayName);
    }

    public function testScopeShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $sharingLink = $this->createMock(SharingLink::class);
        $sharingLink->method('getScope')->willReturn('anonymous');

        $sut = new SharingLinkProxy($graph, $sharingLink);
        $this->assertInternalType('string', $sut->scope);
        $this->assertSame('anonymous', $sut->scope);
    }

    public function testTypeShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $sharingLink = $this->createMock(SharingLink::class);
        $sharingLink->method('getType')->willReturn('view');

        $sut = new SharingLinkProxy($graph, $sharingLink);
        $this->assertInternalType('string', $sut->type);
        $this->assertSame('view', $sut->type);
    }

    public function testWebUrlShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $sharingLink = $this->createMock(SharingLink::class);
        $sharingLink->method('getWebUrl')->willReturn('http://ho.st/web-url');

        $sut = new SharingLinkProxy($graph, $sharingLink);
        $this->assertInternalType('string', $sut->webUrl);
        $this->assertSame('http://ho.st/web-url', $sut->webUrl);
    }
}
