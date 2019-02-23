<?php

namespace Test\Functional\Krizalys\Onedrive;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use GuzzleHttp\Client as GuzzleHttpClient;
use Krizalys\Onedrive\Client;
use Krizalys\Onedrive\DriveItem;
use Krizalys\Onedrive\File;
use Krizalys\Onedrive\Folder;
use Krizalys\Onedrive\Proxy\AudioProxy;
use Krizalys\Onedrive\Proxy\BaseItemProxy;
use Krizalys\Onedrive\Proxy\DeletedProxy;
use Krizalys\Onedrive\Proxy\DriveItemProxy;
use Krizalys\Onedrive\Proxy\DriveProxy;
use Krizalys\Onedrive\Proxy\EntityProxy;
use Krizalys\Onedrive\Proxy\FileProxy;
use Krizalys\Onedrive\Proxy\FileSystemInfoProxy;
use Krizalys\Onedrive\Proxy\FolderProxy;
use Krizalys\Onedrive\Proxy\GeoCoordinatesProxy;
use Krizalys\Onedrive\Proxy\GraphListProxy;
use Krizalys\Onedrive\Proxy\IdentitySetProxy;
use Krizalys\Onedrive\Proxy\ImageProxy;
use Krizalys\Onedrive\Proxy\ItemReferenceProxy;
use Krizalys\Onedrive\Proxy\ListItemProxy;
use Krizalys\Onedrive\Proxy\PackageProxy;
use Krizalys\Onedrive\Proxy\PermissionProxy;
use Krizalys\Onedrive\Proxy\PhotoProxy;
use Krizalys\Onedrive\Proxy\PublicationFacetProxy;
use Krizalys\Onedrive\Proxy\QuotaProxy;
use Krizalys\Onedrive\Proxy\RemoteItemProxy;
use Krizalys\Onedrive\Proxy\RootProxy;
use Krizalys\Onedrive\Proxy\SearchResultProxy;
use Krizalys\Onedrive\Proxy\SharedProxy;
use Krizalys\Onedrive\Proxy\SharepointIdsProxy;
use Krizalys\Onedrive\Proxy\SpecialFolderProxy;
use Krizalys\Onedrive\Proxy\SystemProxy;
use Krizalys\Onedrive\Proxy\ThumbnailProxy;
use Krizalys\Onedrive\Proxy\UserProxy;
use Krizalys\Onedrive\Proxy\VideoProxy;
use Krizalys\Onedrive\Proxy\WorkbookProxy;
use Microsoft\Graph\Graph;
use Monolog\Logger;
use Symfony\Component\Process\Process;

/**
 * @group functional
 */
class KrizalysOnedriveTest extends \PHPUnit_Framework_TestCase
{
    const MICROSOFT_GRAPH_BASE_URI = 'https://graph.microsoft.com/v1.0/';

    const REDIRECT_URI_PORT = 7777;

    const ASYNC_POLL_TIMEOUT = 10; // In seconds.

    const ASYNC_POLL_INTERVAL = 1; // In seconds.

    const DATETIME_REGEX = '/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[-+]\d{4}/';

    const URI_REGEX = '|^([^:/?#]+:)?(//[^/?#]*)?[^?#]*(\?[^#]*)?(#.*)?|';

    const PHP_LOGO_PNG_BASE64 = <<<'EOF'
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
EOF;

    private static $client;

    private static $clientSecret;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $config = sprintf('%s/config.php', __DIR__);

        if (!file_exists($config)) {
            throw new \Exception("Configuration file not found. Please create a $config file from the sample provided.");
        }

        $config = require $config;

        $client = new Client(
            $config['CLIENT_ID'],
            new Graph(),
            new GuzzleHttpClient(
                ['base_uri' => MICROSOFT_GRAPH_BASE_URI]
            ),
            new Logger('Krizalys\Onedrive\Client')
        );

        self::$clientSecret = $config['SECRET'];

        $code = self::getAuthenticationCode(
            $client,
            $config['CLIENT_ID'],
            $config['USERNAME'],
            $config['PASSWORD']
        );

        $client->obtainAccessToken(self::$clientSecret, $code);
        self::$client = $client;
    }

    public function testGetDrives()
    {
        $drives = self::$client->getDrives();
        $this->assertGreaterThanOrEqual(1, count($drives));

        foreach ($drives as $drive) {
            $this->assertDriveProxy($drive);
        }
    }

    public function testGetMyDrive()
    {
        $drive = self::$client->getMyDrive();
        $this->assertDriveProxy($drive);
    }

    public function testGetDriveById()
    {
        $drive = self::getFirstDrive();
        $drive = self::$client->getDriveById($drive->id);
        $this->assertDriveProxy($drive);
    }

    public function testGetDriveByUser()
    {
        $drive = self::getFirstDrive();
        $drive = self::$client->getDriveByUser($drive->owner->user->id);
        $this->assertDriveProxy($drive);
    }

    public function testGetDriveByGroup()
    {
        $drive = self::getFirstDrive();
        $drive = self::$client->getDriveByGroup($drive->owner->user->id);

        if ($drive == $null) {
            $this->markTestSkipped('No drive by group found');
        }

        $this->assertDriveProxy($drive);
    }

    public function testGetDriveBySite()
    {
        $drive = self::getFirstDrive();
        $drive = self::$client->getDriveBySite($drive->owner->user->id);

        if ($drive == $null) {
            $this->markTestSkipped('No drive by site found');
        }

        $this->assertDriveProxy($drive);
    }

    public function testGetDriveItemById()
    {
        $item = self::getRoot();
        $item = self::$client->getDriveItemById($item->parentReference->driveId, $item->id);
        $this->assertDriveItemProxy($item);
    }

    public function testGetRoot()
    {
        $item = self::$client->getRoot();
        $this->assertDriveItemProxy($item);
        $this->assertNotNull($item->parentReference);
        $this->assertNull($item->parentReference->id);
        $this->assertNotNull($item->parentReference->driveId);
        $this->assertNotNull($item->parentReference->driveType);
        $this->assertNull($item->parentReference->path);
        $this->assertRootProxy($item->root);
    }

    /**
     * @dataProvider specialFolderProvider
     */
    public function testGetSpecialFolder($specialFolderName)
    {
        $item = self::$client->getSpecialFolder($specialFolderName);
        $this->assertDriveItemProxy($item);
        $this->assertNotNull($item->parentReference);
        $this->assertNotNull($item->parentReference->id);
        $this->assertNotNull($item->parentReference->driveId);
        $this->assertNotNull($item->parentReference->driveType);
        $this->assertNotNull($item->parentReference->path);

        // For some reason, this special folder does not have a SpecialFolder
        // facet.
        if ($specialFolderName != 'approot') {
            $this->assertSpecialFolderProxy($item->specialFolder);
        }
    }

    public function testGetShared()
    {
        $items = self::$client->getShared();
        $this->assertInternalType('array', $items);

        foreach ($items as $item) {
            $this->assertDriveItemProxy($item);
        }
    }

    public function testGetRecent()
    {
        $items = self::$client->getRecent();
        $this->assertInternalType('array', $items);

        foreach ($items as $item) {
            $this->assertDriveItemProxy($item);
        }
    }

    private function assertRootProxy($root)
    {
        $this->assertInstanceOf(RootProxy::class, $root);
    }

    private function assertSpecialFolderProxy($specialFolder)
    {
        $this->assertInstanceOf(SpecialFolderProxy::class, $specialFolder);
        $this->assertInternalType('string', $specialFolder->name);
    }

    public function testCreateFolder()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $sandbox) {
            // Upload a new file.
            $this->assertCreateFolder($sandbox);

            // Overwrite an existing file.
            $this->assertCreateFolder($sandbox);
        });
    }

    public function testGetChildren()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $sandbox) {
            self::createFolder($sandbox, 'Test folder');
            self::upload($sandbox, 'Test file');
            $children = $sandbox->getChildren();

            foreach ($children as $child) {
                $this->assertDriveItemProxy($child);
            }

            $this->assertCount(2, $children);
            $this->assertEquals('Test folder', $children[0]->name);
            $this->assertEquals('Test file', $children[1]->name);
        });
    }

    public function testDeleteFile()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $sandbox) {
            $item = self::upload($sandbox, 'Test file');
            $item->delete();
            $children = $sandbox->children;
            $this->assertCount(0, $children);
        });
    }

    public function testDeleteFolder()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $sandbox) {
            $item = self::createFolder($sandbox, 'Test folder');
            $item->delete();
            $children = $sandbox->children;
            $this->assertCount(0, $children);
        });
    }

    public function testUploadString()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $sandbox) {
            // Upload a new file.
            $this->assertUploadString($sandbox);

            // Overwrite an existing file.
            $this->assertUploadString($sandbox);
        });
    }

    public function testUploadStream()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $sandbox) {
            // Upload a new file.
            $this->assertUploadStream($sandbox);

            // Overwrite an existing file.
            $this->assertUploadStream($sandbox);
        });
    }

    public function testRename()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $sandbox) {
            $item        = self::upload($sandbox, 'Test file');
            $destination = self::createFolder($sandbox, 'Test destination');

            $item = $item->rename(
                'Test file (renamed)',
                [
                    'description' => 'Test description (updated)',
                ]
            );

            $this->assertDriveItemProxy($item);
            $this->assertNotNull($item->parentReference);
            $this->assertEquals($sandbox->id, $item->parentReference->id);
            $this->assertEquals('Test file (renamed)', $item->name);
            $this->assertEquals('Test description (updated)', $item->description);
        });
    }

    public function testMoveFile()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $sandbox) {
            $item        = self::upload($sandbox, 'Test file');
            $destination = self::createFolder($sandbox, 'Test destination');

            $item = $item->move(
                $destination,
                [
                    'name' => 'Test file (moved)',
                ]
            );

            $this->assertDriveItemProxy($item);
            $this->assertNotNull($item->parentReference);
            $this->assertEquals($destination->id, $item->parentReference->id);
            $this->assertEquals('Test file (moved)', $item->name);
        });
    }

    public function testMoveFolder()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $sandbox) {
            $item        = self::createFolder($sandbox, 'Test folder');
            $destination = self::createFolder($sandbox, 'Test destination');

            $item = $item->move(
                $destination,
                [
                    'name' => 'Test folder (moved)',
                ]
            );

            $children = $sandbox->children;
            $this->assertCount(1, $children);
            $this->assertDriveItemProxy($item);
            $this->assertNotNull($item->parentReference);
            $this->assertEquals($destination->id, $item->parentReference->id);
            $this->assertEquals('Test folder (moved)', $item->name);
        });
    }

    public function testCopyFile()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $sandbox) {
            $item        = self::upload($sandbox, 'Test file');
            $destination = self::createFolder($sandbox, 'Test destination');

            $uri = $item->copy(
                $destination,
                [
                    'name' => 'Test file (copied)',
                ]
            );

            $this->assertRegExp(self::URI_REGEX, $uri);
            $item = $this->assertDriveItemProxyWillHaveChild($destination, 'Test file (copied)');
            $item = self::getDriveItemById($item->id);
            $this->assertDriveItemProxy($item);
            $this->assertNotNull($item->parentReference);
            $this->assertEquals($destination->id, $item->parentReference->id);
            $this->assertEquals('Test file (copied)', $item->name);
        });
    }

    public function testCopyFolder()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $sandbox) {
            $item        = self::createFolder($sandbox, 'Test folder');
            $destination = self::createFolder($sandbox, 'Test destination');

            $uri = $item->copy(
                $destination,
                [
                    'name' => 'Test folder (copied)',
                ]
            );

            $this->assertRegExp(self::URI_REGEX, $uri);
            $item = $this->assertDriveItemProxyWillHaveChild($destination, 'Test folder (copied)');
            $item = self::getDriveItemById($item->id);
            $this->assertDriveItemProxy($item);
            $this->assertNotNull($item->parentReference);
            $this->assertEquals($destination->id, $item->parentReference->id);
            $this->assertEquals('Test folder (copied)', $item->name);
        });
    }

    // Legacy support //////////////////////////////////////////////////////////
    public function testClientRenewAccessTokenLegacy()
    {
        $before = clone self::$client->getState()->token;
        self::$client->renewAccessToken(self::$clientSecret);
        $after = clone self::$client->getState()->token;
        $this->assertNotEquals($before->obtained, $after->data->obtained);
        $this->assertNotEquals($before->data->access_token, $after->data->access_token);
        $this->assertNotEquals($before->data->refresh_token, $after->data->refresh_token);
    }

    public function testClientCreateFolderLegacy()
    {
        $root = self::getRoot();

        $folder1 = self::$client->createFolder('Test folder #1', null, null);
        $this->assertInstanceOf(Folder::class, $folder1);
        $this->assertEquals('Test folder #1', $folder1->getName());

        $children = array_map(function (DriveItemProxy $driveItem) {
            return $driveItem->id;
        }, $root->children);

        $this->assertContains($folder1->getId(), $children);
        self::deleteLegacy($folder1);

        $folder2 = self::$client->createFolder('Test folder #2', null, 'Test description folder #2');
        $this->assertInstanceOf(Folder::class, $folder2);
        $this->assertEquals('Test folder #2', $folder2->getName());

        $children = array_map(function (DriveItemProxy $driveItem) {
            return $driveItem->id;
        }, $root->children);

        $this->assertContains($folder2->getId(), $children);
        $this->assertEquals('Test description folder #2', $folder2->getDescription());
        self::deleteLegacy($folder2);

        self::runInFolder(__FUNCTION__, function (DriveItemProxy $parent) {
            $folder3 = self::$client->createFolder('Test folder #3', $parent->id, null);
            $this->assertInstanceOf(Folder::class, $folder3);
            $this->assertEquals('Test folder #3', $folder3->getName());
            $this->assertEquals($parent->id, $folder3->getParentId());

            $children = array_map(function (DriveItemProxy $driveItem) {
                return $driveItem->id;
            }, $parent->children);

            $this->assertContains($folder3->getId(), $children);
        });
    }

    public function testClientCreateFileLegacy()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $parent) {
            // Test with a text file.
            $file1 = self::$client->createFile('Test file #1.txt', $parent->id, 'Test content');
            $this->assertInstanceOf(File::class, $file1);
            $this->assertEquals('Test file #1.txt', $file1->getName());
            $this->assertEquals($parent->id, $file1->getParentId());
            $this->assertEquals('Test content', $file1->fetchContent());

            $children = array_map(function (DriveItemProxy $driveItem) {
                return $driveItem->id;
            }, $parent->children);

            $this->assertContains($file1->getId(), $children);

            // Test with a binary file.
            $content = base64_decode(self::PHP_LOGO_PNG_BASE64);
            $file2   = self::$client->createFile('Test file #2.png', $parent->id, $content);
            $this->assertInstanceOf(File::class, $file2);
            $this->assertEquals('Test file #2.png', $file2->getName());
            $this->assertEquals($parent->id, $file2->getParentId());
            $this->assertEquals($content, $file2->fetchContent());

            $children = array_map(function (DriveItemProxy $driveItem) {
                return $driveItem->id;
            }, $parent->children);

            $this->assertContains($file2->getId(), $children);
        });
    }

    public function testClientFetchDriveItemLegacy()
    {
        $root = self::getRoot();
        $item = self::$client->fetchDriveItem($root->id);
        $this->assertInstanceOf(Folder::class, $item);
        $this->assertContains($item->getName(), ['root', 'SkyDrive']);
        $this->assertInternalType('string', $item->getParentId());
    }

    public function testClientFetchRootLegacy()
    {
        $root = self::$client->fetchRoot();
        $this->assertInstanceOf(Folder::class, $root);
        $this->assertContains($root->getName(), ['root', 'SkyDrive']);
        $this->assertInternalType('string', $root->getParentId());
    }

    public function testClientFetchCameraRollLegacy()
    {
        $pics       = self::getPhotosFolder();
        $cameraRoll = self::$client->fetchCameraRoll();
        $this->assertInstanceOf(Folder::class, $cameraRoll);
        $this->assertEquals('Camera Roll', $cameraRoll->getName());

        $children = array_map(function (DriveItemProxy $driveItem) {
            return $driveItem->name;
        }, $pics->children);

        $this->assertContains($cameraRoll->getName(), $children);
    }

    public function testClientFetchDocsLegacy()
    {
        $root = self::getRoot();
        $docs = self::$client->fetchDocs();
        $this->assertInstanceOf(Folder::class, $docs);
        $this->assertEquals('Documents', $docs->getName());

        $children = array_map(function (DriveItemProxy $driveItem) {
            return $driveItem->name;
        }, $root->children);

        $this->assertContains($docs->getName(), $children);
    }

    public function testClientFetchPicsLegacy()
    {
        $root = self::getRoot();
        $pics = self::$client->fetchPics();
        $this->assertInstanceOf(Folder::class, $pics);
        $this->assertEquals('Pictures', $pics->getName());

        $children = array_map(function (DriveItemProxy $driveItem) {
            return $driveItem->name;
        }, $root->children);

        $this->assertContains($pics->getName(), $children);
    }

    public function testClientFetchPropertiesLegacy()
    {
        $root       = self::getRoot();
        $properties = self::$client->fetchProperties($root->id);
        $this->assertInternalType('object', $properties);
        $this->assertInternalType('string', $properties->id);
        $this->assertInternalType('object', $properties->from);
        $this->assertNull($properties->from->name);
        $this->assertNull($properties->from->id);
        $this->assertContains($properties->name, ['root', 'SkyDrive']);
        $this->assertEquals('', $properties->description);
        $this->assertInternalType('string', $properties->parent_id);
        $this->assertGreaterThanOrEqual(0, $properties->size);
        $this->assertRegExp(self::DATETIME_REGEX, $properties->created_time);
        $this->assertRegExp(self::DATETIME_REGEX, $properties->updated_time);
    }

    public function testClientFetchDriveItemsLegacy()
    {
        $root  = self::getRoot();
        $items = self::$client->fetchDriveItems($root->id);
        $this->assertInternalType('array', $items);

        foreach ($items as $item) {
            $this->assertThat(
                $item,
                $this->logicalOr(
                    $this->isInstanceOf(Folder::class, $item),
                    $this->isInstanceOf(File::class, $item)
                )
            );
        }
    }

    public function testClientUpdateDriveItemLegacy()
    {
        $root = self::getRoot();
        $item = self::createFolder($root, 'Test folder');

        self::$client->updateDriveItem(
            $item->id,
            [
                'name'        => 'Test folder (renamed)',
                'description' => 'Test description folder',
            ],
            false
        );

        $item = self::getDriveItemById($item->id);
        $this->assertEquals('Test folder (renamed)', $item->name);
        $this->assertEquals('Test description folder', $item->description);

        self::delete($item);
    }

    public function testClientMoveDriveItemLegacy()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $parent) {
            $item        = self::createFolder($parent, 'Test item');
            $destination = self::createFolder($parent, 'Test destination');
            self::$client->moveDriveItem($item->id, $destination->id);

            $children = array_map(function (DriveItemProxy $driveItem) {
                return $driveItem->id;
            }, $parent->children);

            $this->assertFalse(in_array($item->id, $children));

            $children = array_map(function (DriveItemProxy $driveItem) {
                return $driveItem->id;
            }, $destination->children);

            $this->assertContains($item->id, $children);

            $item = self::getDriveItemById($item->id);
            $this->assertEquals($destination->id, $item->parentReference->id);
        });
    }

    public function testClientCopyDriveItemLegacy()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $parent) {
            $item        = self::upload($parent, 'Test item');
            $destination = self::createFolder($parent, 'Test destination');
            self::$client->copyFile($item->id, $destination->id);

            $children = array_map(function (DriveItemProxy $driveItem) {
                return $driveItem->id;
            }, $parent->children);

            $this->assertContains($item->id, $children);
            $this->assertDriveItemProxyWillHaveChild($destination, 'Test item');
        });
    }

    public function testClientDeleteDriveItemLegacy()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $parent) {
            $item = self::createFolder($parent, 'Test folder');
            self::$client->deleteDriveItem($item->id);

            $children = array_map(function (DriveItemProxy $driveItem) {
                return $driveItem->id;
            }, $parent->children);

            $this->assertFalse(in_array($item->id, $children));
        });
    }

    public function testClientFetchQuotaLegacy()
    {
        $quota = self::$client->fetchQuota();
        $this->assertInternalType('object', $quota);
        $this->assertGreaterThanOrEqual(0, $quota->quota);
        $this->assertGreaterThanOrEqual(0, $quota->available);
    }

    public function testClientFetchRecentDocsLegacy()
    {
        $recentDocs = self::$client->fetchRecentDocs();
        $this->assertInternalType('object', $recentDocs);
        $this->assertInternalType('array', $recentDocs->data);

        foreach ($recentDocs->data as $data) {
            $this->assertInternalType('object', $data);
        }
    }

    public function testClientFetchSharedLegacy()
    {
        $shared = self::$client->fetchShared();
        $this->assertInternalType('object', $shared);
        $this->assertInternalType('array', $shared->data);

        foreach ($shared->data as $data) {
            $this->assertInternalType('object', $data);
        }
    }

    public function testDriveItemMoveLegacy()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $parent) {
            $item        = self::upload($parent, 'Test item');
            $destination = self::createFolder($parent, 'Test destination');

            (new File(self::$client, $item->id))->move($destination->id);

            $children = array_map(function (DriveItemProxy $driveItem) {
                return $driveItem->id;
            }, $parent->children);

            $this->assertFalse(in_array($item->id, $children));

            $children = array_map(function (DriveItemProxy $driveItem) {
                return $driveItem->id;
            }, $destination->children);

            $this->assertContains($item->id, $children);

            $item = self::getDriveItemById($item->id);
            $this->assertEquals($destination->id, $item->parentReference->id);
        });
    }

    public function testFileFetchContentLegacy()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $parent) {
            $item   = self::upload($parent, 'Test item', 'Test content');
            $item   = new File(self::$client, $item->id);
            $actual = $item->fetchContent();
            $this->assertEquals('Test content', $actual);
        });
    }

    public function testFileCopyLegacy()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $parent) {
            $item        = self::upload($parent, 'Test item');
            $destination = self::createFolder($parent, 'Test destination');
            (new File(self::$client, $item->id))->copy($destination->id);

            $children = array_map(function (DriveItemProxy $driveItem) {
                return $driveItem->name;
            }, $parent->children);

            $this->assertContains($item->name, $children);
            $this->assertDriveItemProxyWillHaveChild($destination, $item->name);

            $copy = array_filter($destination->children, function (DriveItemProxy $driveItem) use ($item) {
                return $driveItem->name == $item->name;
            });

            $this->assertCount(1, $copy);
            $copy = self::getDriveItemById($copy[0]->id);
            $this->assertEquals($destination->id, $copy->parentReference->id);
        });
    }

    public function testFolderFetchDriveItemsLegacy()
    {
        $root  = self::getRoot();
        $root  = new Folder(self::$client, $root->id);
        $items = $root->fetchDriveItems();
        $this->assertInternalType('array', $items);

        foreach ($items as $item) {
            $this->assertThat(
                $item,
                $this->logicalOr(
                    $this->isInstanceOf(Folder::class, $item),
                    $this->isInstanceOf(File::class, $item)
                )
            );
        }
    }

    public function testFolderFetchChildDriveItemsLegacy()
    {
        $root  = self::getRoot();
        $root  = new Folder(self::$client, $root->id);
        $items = $root->fetchChildDriveItems();
        $this->assertInternalType('array', $items);

        foreach ($items as $item) {
            $this->assertThat(
                $item,
                $this->logicalOr(
                    $this->isInstanceOf(Folder::class, $item),
                    $this->isInstanceOf(File::class, $item)
                )
            );
        }
    }

    public function testFolderCreateFolderLegacy()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $parent) {
            $item = (new Folder(self::$client, $parent->id))->createFolder('Test folder', 'Test description');
            $this->assertInstanceOf(Folder::class, $item);
            $this->assertEquals($parent->id, $item->getParentId());
            $this->assertEquals('Test folder', $item->getName());
            $this->assertEquals('Test description', $item->getDescription());

            $children = array_map(function (DriveItemProxy $driveItem) {
                return $driveItem->id;
            }, $parent->children);

            $this->assertContains($item->getId(), $children);
        });
    }

    public function testFolderCreateFileLegacy()
    {
        self::runInFolder(__FUNCTION__, function (DriveItemProxy $parent) {
            $item = (new Folder(self::$client, $parent->id))->createFile('Test file', 'Test content');
            $this->assertInstanceOf(File::class, $item);
            $this->assertEquals($parent->id, $item->getParentId());
            $this->assertEquals('Test file', $item->getName());
            $this->assertEquals('Test content', $item->fetchContent());

            $children = array_map(function (DriveItemProxy $driveItem) {
                return $driveItem->id;
            }, $parent->children);

            $this->assertContains($item->getId(), $children);
        });
    }

    public function specialFolderProvider()
    {
        return [
            ['documents'],
            ['photos'],
            ['cameraroll'],
            ['approot'],
            ['music'],
        ];
    }

    private function assertEntityProxy($entity)
    {
        $this->assertInstanceOf(EntityProxy::class, $entity);
        $this->assertInternalType('string', $entity->id);
    }

    private function assertBaseItemProxy($baseItem)
    {
        $this->assertEntityProxy($baseItem);
        $this->assertInstanceOf(BaseItemProxy::class, $baseItem);

        $this->assertThat(
            $baseItem->createdBy,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(IdentitySetProxy::class, $baseItem->createdBy)
            )
        );

        $this->assertThat(
            $baseItem->createdDateTime,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(\DateTime::class)
            )
        );

        $this->assertThat(
            $baseItem->description,
            $this->logicalOr(
                $this->isNull(),
                $this->isType('string')
            )
        );

        $this->assertThat(
            $baseItem->eTag,
            $this->logicalOr(
                $this->isNull(),
                $this->isType('string')
            )
        );

        $this->assertThat(
            $baseItem->lastModifiedBy,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(IdentitySetProxy::class, $baseItem->createdBy)
            )
        );

        $this->assertThat(
            $baseItem->lastModifiedDateTime,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(\DateTime::class)
            )
        );

        $this->assertThat(
            $baseName->name,
            $this->logicalOr(
                $this->isNull(),
                $this->isType('string')
            )
        );

        $this->assertThat(
            $baseItem->parentReference,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(ItemReferenceProxy::class, $baseItem->parentReference)
            )
        );

        $this->assertThat(
            $baseItem->webUrl,
            $this->logicalOr(
                $this->isNull(),
                $this->matchesRegularExpression(self::URI_REGEX)
            )
        );

        $this->assertThat(
            $baseItem->createdByUser,
            $this->logicalOr(
                $this->isNull,
                $this->isInstanceOf(UserProxy::class)
            )
        );

        $this->assertThat(
            $baseItem->lastModifiedByUser,
            $this->logicalOr(
                $this->isNull,
                $this->isInstanceOf(UserProxy::class)
            )
        );
    }

    private function assertQuotaProxy($quota)
    {
        $this->assertInstanceOf(QuotaProxy::class, $quota);
        $this->assertGreaterThanOrEqual(0, $quota->deleted);
        $this->assertGreaterThanOrEqual(0, $quota->remaining);
        $this->assertContains($quota->state, ['normal', 'nearing', 'critical', 'exceeded']);
        $this->assertGreaterThanOrEqual(0, $quota->total);
        $this->assertGreaterThanOrEqual(0, $quota->used);
    }

    private function assertPermissionProxy($permission)
    {
        $this->assertInstanceOf(PermissionProxy::class, $permission);
    }

    private function assertThumbnailProxy($thumbnail)
    {
        $this->assertInstanceOf(ThumbnailProxy::class, $thumbnail);
    }

    private function assertDriveProxy($drive)
    {
        $this->assertBaseItemProxy($drive);
        $this->assertInstanceOf(DriveProxy::class, $drive);
        $this->assertContains($drive->driveType, ['personal', 'business', 'documentLibrary']);
        $this->assertInstanceOf(IdentitySetProxy::class, $drive->owner);
        $this->assertQuotaProxy($drive->quota);

        $this->assertThat(
            $drive->sharePointIds,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(SharepointIdsProxy::class)
            )
        );

        $this->assertThat(
            $drive->system,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(SystemProxy::class)
            )
        );

        if ($drive->items !== null) {
            foreach ($drive->items as $item) {
                $this->assertDriveItemProxy($item);
            }
        }

        $this->assertThat(
            $drive->list,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(GraphListProxy::class)
            )
        );

        $this->assertThat(
            $drive->root,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(DriveItemProxy::class)
            )
        );

        $this->assertThat(
            $drive->special,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(DriveItemProxy::class)
            )
        );
    }

    private function assertDriveItemProxy($item)
    {
        $this->assertBaseItemProxy($item);
        $this->assertInstanceOf(DriveItemProxy::class, $item);

        $this->assertThat(
            $item->audio,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(AudioProxy::class)
            )
        );

        //"content" => [ "@odata.type" => "Edm.Stream" ],
        $this->assertNotNull($item->cTag);

        $this->assertThat(
            $item->deleted,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(DeletedProxy::class)
            )
        );

        $this->assertThat(
            $item->file,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(FileProxy::class)
            )
        );

        $this->assertThat(
            $item->fileSystemInfo,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(FileSystemInfoProxy::class)
            )
        );

        $this->assertThat(
            $item->folder,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(FolderProxy::class)
            )
        );

        $this->assertThat(
            $item->image,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(ImageProxy::class)
            )
        );

        $this->assertThat(
            $item->location,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(GeoCoordinatesProxy::class)
            )
        );

        $this->assertThat(
            $item->package,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(PackageProxy::class)
            )
        );

        $this->assertThat(
            $item->photo,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(PhotoProxy::class)
            )
        );

        $this->assertThat(
            $item->publication,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(PublicationFacetProxy::class)
            )
        );

        $this->assertThat(
            $item->remoteItem,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(RemoteItemProxy::class)
            )
        );

        $this->assertThat(
            $item->root,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(RootProxy::class)
            )
        );

        $this->assertThat(
            $item->searchResult,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(SearchResultProxy::class)
            )
        );

        $this->assertThat(
            $item->shared,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(SharedProxy::class)
            )
        );

        $this->assertThat(
            $item->sharepointIds,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(SharepointIdsProxy::class)
            )
        );

        $this->assertThat(
            $item->size,
            $this->logicalOr(
                $this->isNull(),
                $this->greaterThanOrEqual(0)
            )
        );

        $this->assertThat(
            $item->specialFolder,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(SpecialFolderProxy::class)
            )
        );

        $this->assertThat(
            $item->video,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(VideoProxy::class)
            )
        );

        $this->assertThat(
            $item->webDavUrl,
            $this->logicalOr(
                $this->isNull(),
                $this->matchesRegularExpression('|^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?|')
            )
        );

        foreach ($item->children as $child) {
            $this->assertDriveItemProxy($child);
        }

        $this->assertThat(
            $item->listItem,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(ListItemProxy::class)
            )
        );

        if ($item->permissions !== null) {
            foreach ($item->permissions as $permission) {
                $this->assertPermissionProxy($permission);
            }
        }

        if ($item->thumbnails !== null) {
            foreach ($item->thumbnails as $thumbnail) {
                $this->assertThumbnailProxy($thumbnail);
            }
        }

        if ($item->versions !== null) {
            foreach ($item->versions as $version) {
                $this->assertInternalType('string', $version);
            }
        }

        $this->assertThat(
            $item->workbook,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(WorkbookProxy::class)
            )
        );
    }

    private function assertDriveItemProxyWillHaveChild(DriveItemProxy $item, $name)
    {
        $now = time();

        $items = array_filter($item->children, function (DriveItemProxy $item) use ($name) {
            return $item->name == $name;
        });

        while (count($items) != 1) {
            sleep(self::ASYNC_POLL_INTERVAL);

            if (time() - $now > self::ASYNC_POLL_TIMEOUT) {
                $this->fail('Assertion failed after timeout');
            }

            $items = array_filter($item->children, function (DriveItemProxy $item) use ($name) {
                return $item->name == $name;
            });
        }

        return $items[0];
    }

    private function assertCreateFolder(DriveItemProxy $sandbox)
    {
        $item = $sandbox->createFolder(
            'Test folder',
            [
                'description' => 'Test description',
            ]
        );

        $this->assertDriveItemProxy($item);
        $this->assertNotNull($item->parentReference);
        $this->assertEquals($sandbox->id, $item->parentReference->id);
        $this->assertEquals('Test folder', $item->name);
        $this->assertEquals('Test description', $item->description);
    }

    private function assertUploadString(DriveItemProxy $sandbox)
    {
        $item = $sandbox->upload(
            'Test file',
            'Test content',
            [
                'Content-Type: text/plain',
            ]
        );

        $this->assertDriveItemProxy($item);
        $this->assertNotNull($item->parentReference);
        $this->assertEquals($sandbox->id, $item->parentReference->id);
        $this->assertEquals('Test file', $item->name);
        $this->assertEquals('Test content', $item->content);
    }

    private function assertUploadStream(DriveItemProxy $sandbox)
    {
        $content = fopen('php://memory', 'rb+');
        fwrite($content, 'Test content');
        rewind($content);

        $item = $sandbox->upload(
            'Test file',
            $content,
            [
                'Content-Type: text/plain',
            ]
        );

        $this->assertDriveItemProxy($item);
        $this->assertNotNull($item->parentReference);
        $this->assertEquals($sandbox->id, $item->parentReference->id);
        $this->assertEquals('Test file', $item->name);
        $this->assertEquals('Test content', $item->content);

        // No need to fclose $content; it is done internally by Guzzle when
        // instantiating a Guzzle stream from it.
    }

    private static function getRoot()
    {
        return self::$client->getRoot();
    }

    private static function getDriveItemById($id)
    {
        $item = self::getRoot();
        return self::$client->getDriveItemById($item->parentReference->driveId, $id);
    }

    private static function getPhotosFolder()
    {
        return self::$client->getSpecialFolder('photos');
    }

    private static function createFolder(DriveItemProxy $item, $name)
    {
        return $item->createFolder($name);
    }

    private static function upload(DriveItemProxy $item, $name, $content = '')
    {
        return $item->upload(
            $name,
            $content,
            [
                'Content-Type: text/plain',
            ]
        );
    }

    private static function delete(DriveItemProxy $item)
    {
        $item->delete();
    }

    private static function deleteLegacy(DriveItem $item)
    {
        self::$client->deleteDriveItem($item->getId());
    }

    private static function getFirstDrive()
    {
        $drives = self::$client->getDrives();
        return $drives[0];
    }

    private static function runInFolder($name, callable $function)
    {
        $root   = self::getRoot();
        $name   = $name . '_' . gmdate('YmdHis');
        $folder = self::createFolder($root, $name);

        try {
            return $function($folder);
        } catch (\Exception $exception) {
            throw $exception;
        } finally {
            self::delete($folder);
        }
    }

    private static function getAuthenticationCode(Client $client, $clientId, $username, $password)
    {
        $command = sprintf('php -S localhost:%d %s/router.php', self::REDIRECT_URI_PORT, __DIR__);
        $server  = new Process($command);
        $server->start();
        $opts = new ChromeOptions();

        $args = [
            '--headless',
            '--incognito',
        ];

        $opts->addArguments($args);
        $caps = DesiredCapabilities::chrome();
        $caps->setCapability(ChromeOptions::CAPABILITY, $opts);
        $seleniumUrl = sprintf('http://localhost:%d/wd/hub', 4444);
        $redirectUri = sprintf('http://localhost:%d/', self::REDIRECT_URI_PORT);

        $scopes = [
            'files.read',
            'files.read.all',
            'files.readwrite',
            'files.readwrite.all',
            'offline_access',
        ];

        $logInUrl  = $client->getLogInUrl($scopes, $redirectUri);
        $webDriver = RemoteWebDriver::create($seleniumUrl, $caps);
        $webDriver->get($logInUrl);
        $usernameLocator = WebDriverBy::id('i0116');
        $passwordLocator = WebDriverBy::id('i0118');
        $nextLocator     = WebDriverBy::id('idSIButton9');
        $webDriver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated($usernameLocator));
        $webDriver->wait()->until(WebDriverExpectedCondition::visibilityOfElementLocated($usernameLocator));
        $webDriver->findElement($usernameLocator)->sendKeys($username);
        $webDriver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated($nextLocator));
        $webDriver->wait()->until(WebDriverExpectedCondition::visibilityOfElementLocated($nextLocator));
        $webDriver->findElement($nextLocator)->click();
        $webDriver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated($passwordLocator));
        $webDriver->wait()->until(WebDriverExpectedCondition::visibilityOfElementLocated($passwordLocator));
        $webDriver->findElement($passwordLocator)->sendKeys($password);
        $webDriver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated($nextLocator));
        $webDriver->wait()->until(WebDriverExpectedCondition::visibilityOfElementLocated($nextLocator));
        $webDriver->findElement($nextLocator)->click();
        $webDriver->wait()->until(WebDriverExpectedCondition::urlMatches('|^' . preg_quote($redirectUri) . '|'));
        $webDriver->quit();

        foreach ($server as $type => $buffer) {
            if ($type == Process::OUT) {
                $lines = explode("\n", $buffer);
                $code  = self::findAuthenticationCode($lines);

                if ($code !== null) {
                    break;
                }
            } else {
                throw new \Exception($buffer);
            }
        }

        $server->stop();
        return $code;
    }

    private static function findAuthenticationCode($lines)
    {
        foreach ($lines as $line) {
            $line = trim($line);

            if (preg_match('/M[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/', $line)) {
                return $line;
            }
        }
    }
}
