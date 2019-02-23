<?php

namespace Test\Unit\Krizalys\Onedrive\Proxy;

use GuzzleHttp\Psr7\Stream;
use Krizalys\Onedrive\Proxy\AudioProxy;
use Krizalys\Onedrive\Proxy\DeletedProxy;
use Krizalys\Onedrive\Proxy\DriveItemProxy;
use Krizalys\Onedrive\Proxy\DriveItemVersionProxy;
use Krizalys\Onedrive\Proxy\FileProxy;
use Krizalys\Onedrive\Proxy\FileSystemInfoProxy;
use Krizalys\Onedrive\Proxy\FolderProxy;
use Krizalys\Onedrive\Proxy\GeoCoordinatesProxy;
use Krizalys\Onedrive\Proxy\ImageProxy;
use Krizalys\Onedrive\Proxy\ListItemProxy;
use Krizalys\Onedrive\Proxy\PackageProxy;
use Krizalys\Onedrive\Proxy\PermissionProxy;
use Krizalys\Onedrive\Proxy\PhotoProxy;
use Krizalys\Onedrive\Proxy\PublicationFacetProxy;
use Krizalys\Onedrive\Proxy\RemoteItemProxy;
use Krizalys\Onedrive\Proxy\RootProxy;
use Krizalys\Onedrive\Proxy\SearchResultProxy;
use Krizalys\Onedrive\Proxy\SharedProxy;
use Krizalys\Onedrive\Proxy\SharepointIdsProxy;
use Krizalys\Onedrive\Proxy\SpecialFolderProxy;
use Krizalys\Onedrive\Proxy\ThumbnailProxy;
use Krizalys\Onedrive\Proxy\VideoProxy;
use Krizalys\Onedrive\Proxy\WorkbookProxy;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Http\GraphRequest;
use Microsoft\Graph\Http\GraphResponse;
use Microsoft\Graph\Model\Audio;
use Microsoft\Graph\Model\Deleted;
use Microsoft\Graph\Model\DriveItem;
use Microsoft\Graph\Model\DriveItemVersion;
use Microsoft\Graph\Model\File;
use Microsoft\Graph\Model\FileSystemInfo;
use Microsoft\Graph\Model\Folder;
use Microsoft\Graph\Model\GeoCoordinates;
use Microsoft\Graph\Model\Image;
use Microsoft\Graph\Model\ItemReference;
use Microsoft\Graph\Model\ListItem;
use Microsoft\Graph\Model\Package;
use Microsoft\Graph\Model\Permission;
use Microsoft\Graph\Model\Photo;
use Microsoft\Graph\Model\PublicationFacet;
use Microsoft\Graph\Model\RemoteItem;
use Microsoft\Graph\Model\Root;
use Microsoft\Graph\Model\SearchResult;
use Microsoft\Graph\Model\Shared;
use Microsoft\Graph\Model\SharepointIds;
use Microsoft\Graph\Model\SpecialFolder;
use Microsoft\Graph\Model\Thumbnail;
use Microsoft\Graph\Model\Video;
use Microsoft\Graph\Model\Workbook;

class DriveItemProxyTest extends \PHPUnit_Framework_TestCase
{
    const DRIVE_ITEM_ID = '0123';

    public function testAudioShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $audio     = $this->createMock(Audio::class);
        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getAudio')->willReturn($audio);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(AudioProxy::class, $sut->audio);
    }

    public function testContentShouldReturnExpectedValue()
    {
        $this->testDownloadShouldReturnExpectedValue();
    }

    public function testCTagShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getCTag')->willReturn('1234');
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInternalType('string', $sut->cTag);
        $this->assertSame('1234', $sut->cTag);
    }

    public function testDeletedShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $deleted   = $this->createMock(Deleted::class);
        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getDeleted')->willReturn($deleted);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(DeletedProxy::class, $sut->deleted);
    }

    public function testFileShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $file      = $this->createMock(File::class);
        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getFile')->willReturn($file);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(FileProxy::class, $sut->file);
    }

    public function testFileSystemInfoShouldReturnExpectedValue()
    {
        $graph          = $this->createMock(Graph::class);
        $fileSystemInfo = $this->createMock(FileSystemInfo::class);
        $driveItem      = $this->createMock(DriveItem::class);
        $driveItem->method('getFileSystemInfo')->willReturn($fileSystemInfo);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(FileSystemInfoProxy::class, $sut->fileSystemInfo);
    }

    public function testFolderShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $folder    = $this->createMock(Folder::class);
        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getFolder')->willReturn($folder);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(FolderProxy::class, $sut->folder);
    }

    public function testImageShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $image     = $this->createMock(Image::class);
        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getImage')->willReturn($image);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(ImageProxy::class, $sut->image);
    }

    public function testLocationShouldReturnExpectedValue()
    {
        $graph          = $this->createMock(Graph::class);
        $geoCoordinates = $this->createMock(GeoCoordinates::class);
        $driveItem      = $this->createMock(DriveItem::class);
        $driveItem->method('getLocation')->willReturn($geoCoordinates);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(GeoCoordinatesProxy::class, $sut->location);
    }

    public function testPackageShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $package   = $this->createMock(Package::class);
        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getPackage')->willReturn($package);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(PackageProxy::class, $sut->package);
    }

    public function testPhotoShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $photo     = $this->createMock(Photo::class);
        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getPhoto')->willReturn($photo);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(PhotoProxy::class, $sut->photo);
    }

    public function testPublicationShouldReturnExpectedValue()
    {
        $graph            = $this->createMock(Graph::class);
        $publicationFacet = $this->createMock(PublicationFacet::class);
        $driveItem        = $this->createMock(DriveItem::class);
        $driveItem->method('getPublication')->willReturn($publicationFacet);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(PublicationFacetProxy::class, $sut->publication);
    }

    public function testRemoteItemShouldReturnExpectedValue()
    {
        $graph      = $this->createMock(Graph::class);
        $remoteItem = $this->createMock(RemoteItem::class);
        $driveItem  = $this->createMock(DriveItem::class);
        $driveItem->method('getRemoteItem')->willReturn($remoteItem);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(RemoteItemProxy::class, $sut->remoteItem);
    }

    public function testRootShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $root      = $this->createMock(Root::class);
        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getRoot')->willReturn($root);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(RootProxy::class, $sut->root);
    }

    public function testSearchResultShouldReturnExpectedValue()
    {
        $graph        = $this->createMock(Graph::class);
        $searchResult = $this->createMock(SearchResult::class);
        $driveItem    = $this->createMock(DriveItem::class);
        $driveItem->method('getSearchResult')->willReturn($searchResult);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(SearchResultProxy::class, $sut->searchResult);
    }

    public function testSharedShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $shared    = $this->createMock(Shared::class);
        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getShared')->willReturn($shared);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(SharedProxy::class, $sut->shared);
    }

    public function testSharepointIdsShouldReturnExpectedValue()
    {
        $graph         = $this->createMock(Graph::class);
        $sharepointIds = $this->createMock(SharepointIds::class);
        $driveItem     = $this->createMock(DriveItem::class);
        $driveItem->method('getSharepointIds')->willReturn($sharepointIds);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(SharepointIdsProxy::class, $sut->sharepointIds);
    }

    public function testSizeShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getSize')->willReturn(1234);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInternalType('int', $sut->size);
        $this->assertSame(1234, $sut->size);
    }

    public function testSpecialFolderShouldReturnExpectedValue()
    {
        $graph         = $this->createMock(Graph::class);
        $specialFolder = $this->createMock(SpecialFolder::class);
        $driveItem     = $this->createMock(DriveItem::class);
        $driveItem->method('getSpecialFolder')->willReturn($specialFolder);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(SpecialFolderProxy::class, $sut->specialFolder);
    }

    public function testVideoShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $video     = $this->createMock(Video::class);
        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getVideo')->willReturn($video);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(VideoProxy::class, $sut->video);
    }

    public function testWebDavUrlShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getWebDavUrl')->willReturn('http://webd.av/url');
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInternalType('string', $sut->webDavUrl);
        $this->assertSame('http://webd.av/url', $sut->webDavUrl);
    }

    public function testChildrenShouldReturnExpectedValue()
    {
        $this->testGetChildrenShouldReturnExpectedValue();
    }

    public function testListItemShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $listItem  = $this->createMock(ListItem::class);
        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getListItem')->willReturn($listItem);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(ListItemProxy::class, $sut->listItem);
    }

    public function testPermissionsShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $permissions = [
            $this->createMock(Permission::class),
            $this->createMock(Permission::class),
        ];

        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getPermissions')->willReturn($permissions);
        $sut    = new DriveItemProxy($graph, $driveItem);
        $actual = $sut->permissions;
        $this->assertInternalType('array', $actual);
        $this->assertCount(2, $actual);

        foreach ($actual as $permission) {
            $this->assertInstanceOf(PermissionProxy::class, $permission);
        }
    }

    public function testThumbnailsShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $thumbnails = [
            $this->createMock(Thumbnail::class),
            $this->createMock(Thumbnail::class),
        ];

        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getThumbnails')->willReturn($thumbnails);
        $sut    = new DriveItemProxy($graph, $driveItem);
        $actual = $sut->thumbnails;
        $this->assertInternalType('array', $actual);
        $this->assertCount(2, $actual);

        foreach ($actual as $thumbnail) {
            $this->assertInstanceOf(ThumbnailProxy::class, $thumbnail);
        }
    }

    public function testVersionsShouldReturnExpectedValue()
    {
        $graph = $this->createMock(Graph::class);

        $driveItemVersions = [
            $this->createMock(DriveItemVersion::class),
            $this->createMock(DriveItemVersion::class),
        ];

        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getVersions')->willReturn($driveItemVersions);
        $sut    = new DriveItemProxy($graph, $driveItem);
        $actual = $sut->versions;
        $this->assertInternalType('array', $actual);
        $this->assertCount(2, $actual);

        foreach ($actual as $driveItemVersion) {
            $this->assertInstanceOf(DriveItemVersionProxy::class, $driveItemVersion);
        }
    }

    public function testWorkbookShouldReturnExpectedValue()
    {
        $graph     = $this->createMock(Graph::class);
        $workbook  = $this->createMock(Workbook::class);
        $driveItem = $this->createMock(DriveItem::class);
        $driveItem->method('getWorkbook')->willReturn($workbook);
        $sut = new DriveItemProxy($graph, $driveItem);
        $this->assertInstanceOf(WorkbookProxy::class, $sut->workbook);
    }

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
