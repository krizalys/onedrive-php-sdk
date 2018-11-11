<?php

use Krizalys\Onedrive\Proxy\DriveItemProxy;
use Krizalys\Onedrive\Proxy\DriveProxy;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Http\GraphRequest;
use Microsoft\Graph\Http\GraphResponse;
use Microsoft\Graph\Model\Drive;
use Microsoft\Graph\Model\DriveItem;

class DriveProxyTest extends \PHPUnit_Framework_TestCase
{
    const DRIVE_ITEM_ID = '0123';

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
