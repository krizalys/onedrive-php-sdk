<?php

namespace Test\Unit\Krizalys\Onedrive\Proxy;

use GuzzleHttp\Psr7\Stream;
use Krizalys\Onedrive\Proxy\DriveItemProxy;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Http\GraphRequest;
use Microsoft\Graph\Http\GraphResponse;
use Microsoft\Graph\Model\DriveItem;
use Microsoft\Graph\Model\ItemReference;

class DriveItemProxyTest extends \PHPUnit_Framework_TestCase
{
    const DRIVE_ITEM_ID = '0123';

    public function testCreateFolderShouldReturnExpectedValue()
    {
        $item      = $this->mockDriveItem();
        $childItem = $this->mockDriveItem(['id' => self::DRIVE_ITEM_ID]);
        $graph     = $this->mockGraphWithResponse(201, ['body' => $childItem]);
        $sut       = new DriveItemProxy($graph, $item);
        $actual    = $sut->createFolder('Irrelevant', []);
        $this->assertInstanceOf(DriveItemProxy::class, $actual);
        $this->assertSame(self::DRIVE_ITEM_ID, $actual->id);
    }

    public function testGetChildrenShouldReturnExpectedValue()
    {
        $childItems = [
            $this->mockDriveItem(['id' => '0001']),
            $this->mockDriveItem(['id' => '0002']),
        ];

        $item   = $this->mockDriveItem();
        $graph  = $this->mockGraphWithCollectionResponse($childItems);
        $sut    = new DriveItemProxy($graph, $item);
        $actual = $sut->getChildren();
        $this->assertInternalType('array', $actual);
        $this->assertCount(2, $actual);

        foreach ($actual as $child) {
            $this->assertInstanceOf(DriveItemProxy::class, $child);
        }

        $this->assertSame('0001', $actual[0]->id);
        $this->assertSame('0002', $actual[1]->id);
    }

    public function testUploadShouldReturnExpectedValue()
    {
        $item      = $this->mockDriveItem();
        $childItem = $this->mockDriveItem(['id' => self::DRIVE_ITEM_ID]);
        $graph     = $this->mockGraphWithResponse(201, ['body' => $childItem]);
        $sut       = new DriveItemProxy($graph, $item);
        $actual    = $sut->upload('Irrelevant', 'Test content', []);
        $this->assertInstanceOf(DriveItemProxy::class, $actual);
        $this->assertSame(self::DRIVE_ITEM_ID, $actual->id);
    }

    public function testDownloadShouldReturnExpectedValue()
    {
        $item     = $this->mockDriveItem();
        $expected = $this->mockStream();
        $graph    = $this->mockGraphWithResponse(200, ['body' => $expected]);
        $sut      = new DriveItemProxy($graph, $item);
        $actual   = $sut->download();
        $this->assertSame($expected, $actual);
    }

    public function testRenameShouldReturnExpectedValue()
    {
        $item        = $this->mockDriveItem();
        $renamedItem = $this->mockDriveItem(['id' => self::DRIVE_ITEM_ID]);
        $graph       = $this->mockGraphWithResponse(200, ['body' => $renamedItem]);
        $sut         = new DriveItemProxy($graph, $item);
        $actual      = $sut->rename('Irrelevant', []);
        $this->assertInstanceOf(DriveItemProxy::class, $actual);
        $this->assertSame($actual->id, self::DRIVE_ITEM_ID);
    }

    public function testMoveShouldReturnExpectedValue()
    {
        $item            = $this->mockDriveItem();
        $movedItem       = $this->mockDriveItem(['id' => self::DRIVE_ITEM_ID]);
        $destinationItem = $this->mockDriveItemProxy();
        $graph           = $this->mockGraphWithResponse(200, ['body' => $movedItem]);
        $sut             = new DriveItemProxy($graph, $item);
        $actual          = $sut->move($destinationItem, []);
        $this->assertInstanceOf(DriveItemProxy::class, $actual);
        $this->assertSame($actual->id, self::DRIVE_ITEM_ID);
    }

    public function testCopyShouldReturnExpectedValue()
    {
        $item            = $this->mockDriveItem();
        $destinationItem = $this->mockDriveItemProxy();
        $graph           = $this->mockGraphWithResponse(202, ['headers' => ['Location' => ['http://progre.ss/url']]]);
        $sut             = new DriveItemProxy($graph, $item);
        $actual          = $sut->copy($destinationItem, []);
        $this->assertInternalType('string', $actual);
        $this->assertSame('http://progre.ss/url', $actual);
    }

    private function mockStream()
    {
        $stream = $this->createMock(Stream::class);

        return $stream;
    }

    private function mockGraphWithResponse($status, array $options = [])
    {
        $response = $this->createMock(GraphResponse::class);
        $response->method('getStatus')->willReturn((string) $status);

        if (array_key_exists('headers', $options)) {
            $response->method('getHeaders')->willReturn($options['headers']);
        }

        if (array_key_exists('body', $options)) {
            $response->method('getResponseAsObject')->willReturn($options['body']);
        }

        $request = $this->createMock(GraphRequest::class);
        $request->method('execute')->willReturn($response);
        $graph = $this->createMock(Graph::class);
        $graph->method('createRequest')->willReturn($request);
        $request->method('addHeaders')->willReturnSelf();
        $request->method('attachBody')->willReturnSelf();

        return $graph;
    }

    private function mockGraphWithCollectionResponse(array $body)
    {
        $response = $this->createMock(GraphResponse::class);
        $response->method('getStatus')->willReturn('200');
        $response->method('getResponseAsObject')->willReturn($body);
        $request = $this->createMock(GraphRequest::class);
        $request->method('execute')->willReturn($response);
        $graph = $this->createMock(Graph::class);
        $graph->method('createCollectionRequest')->willReturn($request);

        return $graph;
    }

    private function mockDriveItem(array $options = [])
    {
        $itemReference = $this->createMock(ItemReference::class);
        $itemReference->method('getDriveId')->willReturn('');
        $item = $this->createMock(DriveItem::class);
        $item->method('getParentReference')->willReturn($itemReference);

        if (array_key_exists('id', $options)) {
            $item->method('getId')->willReturn($options['id']);
        }

        return $item;
    }

    private function mockDriveItemProxy()
    {
        $driveItemProxy = $this->createMock(DriveItemProxy::class);

        return $driveItemProxy;
    }
}
