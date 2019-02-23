<?php

namespace Test\Unit\Krizalys\Onedrive\Proxy;

use Krizalys\Onedrive\Proxy\DriveItemProxy;
use Krizalys\Onedrive\Proxy\DriveProxy;
use Krizalys\Onedrive\Proxy\GraphListProxy;
use Krizalys\Onedrive\Proxy\IdentitySetProxy;
use Krizalys\Onedrive\Proxy\QuotaProxy;
use Krizalys\Onedrive\Proxy\SharepointIdsProxy;
use Krizalys\Onedrive\Proxy\SystemFacetProxy;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Http\GraphRequest;
use Microsoft\Graph\Http\GraphResponse;
use Microsoft\Graph\Model\Drive;
use Microsoft\Graph\Model\DriveItem;
use Microsoft\Graph\Model\GraphList;
use Microsoft\Graph\Model\Identity;
use Microsoft\Graph\Model\IdentitySet;
use Microsoft\Graph\Model\Quota;
use Microsoft\Graph\Model\SharepointIds;
use Microsoft\Graph\Model\SystemFacet;

class DriveProxyTest extends \PHPUnit_Framework_TestCase
{
    const DRIVE_ITEM_ID = '0123';

    public function testDriveTypeShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);
        $drive = $this->createMock(Drive::class);
        $drive->method('getDriveType')->willReturn('personal');
        $sut = new DriveProxy($graph, $drive);
        $this->assertInternalType('string', $sut->driveType);
        $this->assertSame('personal', $sut->driveType);
    }

    public function testOwnerShouldReturnExpectedValue()
    {
        $graph    = $this->createMock(Graph::class);
        $identity = $this->createMock(Identity::class);
        $identity->method('getDisplayName')->willReturn('Display Name');
        $identitySet = $this->createMock(IdentitySet::class);
        $identitySet->method('getUser')->willReturn($identity);
        $drive = $this->createMock(Drive::class);
        $drive->method('getOwner')->willReturn($identitySet);
        $sut = new DriveProxy($graph, $drive);
        $this->assertInstanceOf(IdentitySetProxy::class, $sut->owner);
        $this->assertSame('Display Name', $sut->owner->user->displayName);
    }

    public function testQuotaShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);
        $quota = $this->createMock(Quota::class);
        $quota->method('getTotal')->willReturn(1234);
        $drive = $this->createMock(Drive::class);
        $drive->method('getQuota')->willReturn($quota);
        $sut = new DriveProxy($graph, $drive);
        $this->assertInstanceOf(QuotaProxy::class, $sut->quota);
        $this->assertSame(1234, $sut->quota->total);
    }

    public function testSharePointIdsShouldReturnExpectedValue()
    {
        $graph         = $this->createMock(Graph::class);
        $sharepointIds = $this->createMock(SharepointIds::class);
        $drive         = $this->createMock(Drive::class);
        $drive->method('getSharePointIds')->willReturn($sharepointIds);
        $sut = new DriveProxy($graph, $drive);
        $this->assertInstanceOf(SharepointIdsProxy::class, $sut->sharePointIds);
    }

    public function testSystemShouldReturnExpectedValue()
    {
        $graph       = $this->createMock(Graph::class);
        $systemFacet = $this->createMock(SystemFacet::class);
        $drive       = $this->createMock(Drive::class);
        $drive->method('getSystem')->willReturn($systemFacet);
        $sut = new DriveProxy($graph, $drive);
        $this->assertInstanceOf(SystemFacetProxy::class, $sut->system);
    }

    public function testItemsShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $items = [
            $this->mockDriveItem('0001'),
            $this->mockDriveItem('0002'),
        ];

        $drive = $this->createMock(Drive::class);
        $drive->method('getItems')->willReturn($items);
        $sut    = new DriveProxy($graph, $drive);
        $actual = $sut->items;
        $this->assertInternalType('array', $actual);
        $this->assertCount(2, $actual);

        foreach ($actual as $item) {
            $this->assertInstanceOf(DriveItemProxy::class, $item);
        }

        $this->assertSame('0001', $actual[0]->id);
        $this->assertSame('0002', $actual[1]->id);
    }

    public function testListShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $graphList = $this->createMock(GraphList::class);
        $drive     = $this->createMock(Drive::class);
        $drive->method('getList')->willReturn($graphList);
        $sut   = new DriveProxy($graph, $drive);
        $this->assertInstanceOf(GraphListProxy::class, $sut->list);
    }

    public function testRootShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getId')->willReturn('1234');
        $drive = $this->createMock(Drive::class);
        $drive->method('getRoot')->willReturn($driveItem);
        $sut = new DriveProxy($graph, $drive);
        $this->assertInstanceOf(DriveItemProxy::class, $sut->root);
        $this->assertSame('1234', $sut->root->id);
    }

    public function testSpecialShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getId')->willReturn('1234');
        $drive = $this->createMock(Drive::class);
        $drive->method('getSpecial')->willReturn($driveItem);
        $sut = new DriveProxy($graph, $drive);
        $this->assertInstanceOf(DriveItemProxy::class, $sut->special);
        $this->assertSame('1234', $sut->special->id);
    }

    public function testGetRootShouldReturnExpectedValue()
    {
        $item   = $this->mockDriveItem(self::DRIVE_ITEM_ID);
        $graph  = $this->mockGraphWithResponse($item);
        $drive  = $this->createMock(Drive::class);
        $sut    = new DriveProxy($graph, $drive);
        $actual = $sut->getRoot();
        $this->assertInstanceOf(DriveItemProxy::class, $actual);
        $this->assertSame(self::DRIVE_ITEM_ID, $actual->id);
    }

    private function mockGraphWithResponse($payload)
    {
        $response = $this->createMock(GraphResponse::class);
        $response->method('getStatus')->willReturn('200');
        $response->method('getResponseAsObject')->willReturn($payload);
        $request = $this->createMock(GraphRequest::class);
        $request->method('execute')->willReturn($response);
        $graph = $this->createMock(Graph::class);
        $graph->method('createRequest')->willReturn($request);

        return $graph;
    }

    private function mockDriveItem($id)
    {
        $item = $this->createMock(DriveItem::class);
        $item->method('getId')->willReturn($id);

        return $item;
    }
}
