<?php

namespace Test\Functional\Krizalys\Onedrive\Proxy;

use Krizalys\Onedrive\Proxy\DriveItemProxy;
use Test\Functional\Krizalys\Onedrive\AssertionsTrait;
use Test\Functional\Krizalys\Onedrive\AsynchronousTrait;
use Test\Functional\Krizalys\Onedrive\ClientFactoryTrait;
use Test\Functional\Krizalys\Onedrive\ConfigurationTrait;
use Test\Functional\Krizalys\Onedrive\HttpJsonTrait;
use Test\Functional\Krizalys\Onedrive\OnedriveSandboxTrait;

class DriveItemProxyTest extends \PHPUnit_Framework_TestCase
{
    use AssertionsTrait;
    use AsynchronousTrait;
    use ClientFactoryTrait;
    use ConfigurationTrait;
    use HttpJsonTrait;
    use OnedriveSandboxTrait;

    private static $driveItem;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $clientId = self::getConfig('CLIENT_ID');
        $username = self::getConfig('USERNAME');
        $password = self::getConfig('PASSWORD');
        $secret   = self::getConfig('SECRET');

        $client = self::createClient(
            $clientId,
            $username,
            $password,
            $secret
        );

        self::$driveItem = $client->getRoot();
    }

    public function testCreateFolderWhenNotExisting()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_' . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $driveItem = $sandbox->createFolder(
                'Test folder',
                ['description' => 'Test description']
            );

            $this->assertDriveItemProxy($driveItem);
            $this->assertNotNull($driveItem->parentReference);
            $this->assertEquals($sandbox->id, $driveItem->parentReference->id);
            $this->assertEquals('Test folder', $driveItem->name);
            $this->assertEquals('Test description', $driveItem->description);
        });
    }

    public function testCreateFolderWhenExisting()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_' . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $sandbox->createFolder('Test folder');

            $driveItem = $sandbox->createFolder(
                'Test folder',
                ['description' => 'Test description']
            );

            $this->assertDriveItemProxy($driveItem);
            $this->assertNotNull($driveItem->parentReference);
            $this->assertEquals($sandbox->id, $driveItem->parentReference->id);
            $this->assertEquals('Test folder', $driveItem->name);
            $this->assertEquals('Test description', $driveItem->description);
        });
    }

    public function testGetChildren()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_' . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $sandbox->createFolder('Test folder');

            for ($i = 1; $i <= 2; ++$i) {
                $sandbox->upload(
                    "Test file #$i",
                    "Test content #$i",
                    ['contentType' => 'text/plain']
                );
            }

            $children = $sandbox->getChildren(
                [
                    'top' => 2,

                    'orderBy' => [
                        ['name', 'desc'],
                    ],
                ]
            );

            foreach ($children as $child) {
                $this->assertDriveItemProxy($child);
            }

            $this->assertCount(2, $children);
            $this->assertEquals('Test folder', $children[0]->name);
            $this->assertEquals('Test file #2', $children[1]->name);
        });
    }

    public function testDeleteFile()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_'  . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $driveItem = $sandbox->upload(
                'Test file',
                'Test content',
                ['contentType' => 'text/plain']
            );

            $driveItem->delete();
            $children = $sandbox->children;
            $this->assertCount(0, $children);
        });
    }

    public function testDeleteFolder()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_'  . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $driveItem = $sandbox->createFolder('Test folder');
            $driveItem->delete();
            $children = $sandbox->children;
            $this->assertCount(0, $children);
        });
    }

    public function testUploadStringWhenNotExisting()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_'  . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $driveItem = $sandbox->upload(
                'Test file',
                'Test content',
                ['contentType' => 'text/plain']
            );

            $this->assertDriveItemProxy($driveItem);
            $this->assertNotNull($driveItem->parentReference);
            $this->assertEquals($sandbox->id, $driveItem->parentReference->id);
            $this->assertEquals('Test file', $driveItem->name);
            $this->assertEquals('Test content', $driveItem->content);
        });
    }

    public function testUploadStringWhenExisting()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_'  . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $sandbox->upload(
                'Test file',
                'Test content',
                ['contentType' => 'text/plain']
            );

            $driveItem = $sandbox->upload(
                'Test file',
                'Test content',
                ['contentType' => 'text/plain']
            );

            $this->assertDriveItemProxy($driveItem);
            $this->assertNotNull($driveItem->parentReference);
            $this->assertEquals($sandbox->id, $driveItem->parentReference->id);
            $this->assertEquals('Test file', $driveItem->name);
            $this->assertEquals('Test content', $driveItem->content);
        });
    }

    public function testUploadStreamWhenNotExisting()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_'  . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $content = fopen('php://memory', 'rb+');
            fwrite($content, 'Test content');
            rewind($content);

            $driveItem = $sandbox->upload(
                'Test file',
                $content,
                ['contentType' => 'text/plain']
            );

            $this->assertDriveItemProxy($driveItem);
            $this->assertNotNull($driveItem->parentReference);
            $this->assertEquals($sandbox->id, $driveItem->parentReference->id);
            $this->assertEquals('Test file', $driveItem->name);
            $this->assertEquals('Test content', $driveItem->content);

            // No need to fclose $content; it is done internally by Guzzle when
            // instantiating a Guzzle stream from it.
        });
    }

    public function testUploadStreamWhenExisting()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_'  . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $sandbox->upload(
                'Test file',
                'Test content',
                ['contentType' => 'text/plain']
            );

            $content = fopen('php://memory', 'rb+');
            fwrite($content, 'Test content');
            rewind($content);

            $driveItem = $sandbox->upload(
                'Test file',
                $content,
                ['contentType' => 'text/plain']
            );

            $this->assertDriveItemProxy($driveItem);
            $this->assertNotNull($driveItem->parentReference);
            $this->assertEquals($sandbox->id, $driveItem->parentReference->id);
            $this->assertEquals('Test file', $driveItem->name);
            $this->assertEquals('Test content', $driveItem->content);

            // No need to fclose $content; it is done internally by Guzzle when
            // instantiating a Guzzle stream from it.
        });
    }

    public function testStartUploadStringWhenNotExisting()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_'  . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $string = str_repeat("Test content\n", 100000);

            $uploadSession = $sandbox->startUpload(
                'Test file',
                $string,
                ['contentType' => 'text/plain']
            );

            $this->assertUploadSessionProxy($uploadSession);
            $driveItem = $uploadSession->complete();
            $this->assertDriveItemProxy($driveItem);
            $this->assertNotNull($driveItem->parentReference);
            $this->assertEquals($sandbox->id, $driveItem->parentReference->id);
            $this->assertEquals('Test file', $driveItem->name);
            $this->assertEquals($string, $driveItem->content);
        });
    }

    public function testStartUploadStringWhenExisting()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_'  . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $sandbox->upload(
                'Test file',
                'Test content',
                ['contentType' => 'text/plain']
            );

            $string = str_repeat("Test content\n", 100000);

            $uploadSession = $sandbox->startUpload(
                'Test file',
                $string,
                ['contentType' => 'text/plain']
            );

            $this->assertUploadSessionProxy($uploadSession);
            $driveItem = $uploadSession->complete();
            $this->assertDriveItemProxy($driveItem);
            $this->assertNotNull($driveItem->parentReference);
            $this->assertEquals($sandbox->id, $driveItem->parentReference->id);
            $this->assertEquals('Test file', $driveItem->name);
            $this->assertEquals($string, $driveItem->content);
        });
    }

    public function testStartUploadStreamWhenNotExisting()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_'  . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $content = str_repeat("Test content\n", 100000);
            $stream  = fopen('php://memory', 'rb+');
            fwrite($stream, $content);
            rewind($stream);

            $uploadSession = $sandbox->startUpload(
                'Test file',
                $stream,
                ['contentType' => 'text/plain']
            );

            $this->assertUploadSessionProxy($uploadSession);
            $driveItem = $uploadSession->complete();
            $this->assertDriveItemProxy($driveItem);
            $this->assertNotNull($driveItem->parentReference);
            $this->assertEquals($sandbox->id, $driveItem->parentReference->id);
            $this->assertEquals('Test file', $driveItem->name);
            $this->assertEquals($content, $driveItem->content);

            // No need to fclose $stream; it is done internally by Guzzle when
            // instantiating a Guzzle stream from it.
        });
    }

    public function testStartUploadStreamWhenExisting()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_'  . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $sandbox->upload(
                'Test file',
                'Test content',
                ['contentType' => 'text/plain']
            );

            $content = str_repeat("Test content\n", 100000);
            $stream  = fopen('php://memory', 'rb+');
            fwrite($stream, $content);
            rewind($stream);

            $uploadSession = $sandbox->startUpload(
                'Test file',
                $stream,
                ['contentType' => 'text/plain']
            );

            $this->assertUploadSessionProxy($uploadSession);
            $driveItem = $uploadSession->complete();
            $this->assertDriveItemProxy($driveItem);
            $this->assertNotNull($driveItem->parentReference);
            $this->assertEquals($sandbox->id, $driveItem->parentReference->id);
            $this->assertEquals('Test file', $driveItem->name);
            $this->assertEquals($content, $driveItem->content);

            // No need to fclose $stream; it is done internally by Guzzle when
            // instantiating a Guzzle stream from it.
        });
    }

    public function testRename()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_'  . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $driveItem = $sandbox->upload(
                'Test file',
                'Test content',
                ['contentType' => 'text/plain']
            );

            $destination = $sandbox->createFolder('Test destination');

            $driveItem = $driveItem->rename(
                'Test file (renamed)',
                ['description' => 'Test description (updated)']
            );

            $this->assertDriveItemProxy($driveItem);
            $this->assertNotNull($driveItem->parentReference);
            $this->assertEquals($sandbox->id, $driveItem->parentReference->id);
            $this->assertEquals('Test file (renamed)', $driveItem->name);
            $this->assertEquals('Test description (updated)', $driveItem->description);
        });
    }

    public function testMoveFile()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_'  . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $driveItem = $sandbox->upload(
                'Test file',
                'Test content',
                ['contentType' => 'text/plain']
            );

            $destination = $sandbox->createFolder('Test destination');

            $driveItem = $driveItem->move(
                $destination,
                ['name' => 'Test file (moved)']
            );

            $this->assertDriveItemProxy($driveItem);
            $this->assertNotNull($driveItem->parentReference);
            $this->assertEquals($destination->id, $driveItem->parentReference->id);
            $this->assertEquals('Test file (moved)', $driveItem->name);
        });
    }

    public function testMoveFolder()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_'  . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $driveItem   = $sandbox->createFolder('Test folder');
            $destination = $sandbox->createFolder('Test destination');

            $driveItem = $driveItem->move(
                $destination,
                ['name' => 'Test folder (moved)']
            );

            $children = $sandbox->children;
            $this->assertCount(1, $children);
            $this->assertDriveItemProxy($driveItem);
            $this->assertNotNull($driveItem->parentReference);
            $this->assertEquals($destination->id, $driveItem->parentReference->id);
            $this->assertEquals('Test folder (moved)', $driveItem->name);
        });
    }

    public function testCopyFile()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_' . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $driveItem = $sandbox->upload(
                'Test file',
                'Test content',
                ['contentType' => 'text/plain']
            );

            $destination = $sandbox->createFolder('Test destination');

            $uri = $driveItem->copy(
                $destination,
                ['name' => 'Test file (copied)']
            );

            self::waitUntil(function () use ($uri) {
                return $this->getAndDecode($uri)->status == 'completed';
            });

            $this->assertRegExp(self::$uriRegex, $uri);
            $driveItems = $destination->getChildren(['top' => 2]);
            $this->assertCount(1, $driveItems);
            $driveItem = $driveItems[0];
            $this->assertDriveItemProxy($driveItem);
            $this->assertNotNull($driveItem->parentReference);
            $this->assertEquals($destination->id, $driveItem->parentReference->id);
            $this->assertEquals('Test file (copied)', $driveItem->name);
        });
    }

    public function testCopyFolder()
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_' . __FUNCTION__, function (DriveItemProxy $sandbox) {
            $driveItem   = $sandbox->createFolder('Test folder');
            $destination = $sandbox->createFolder('Test destination');

            $uri = $driveItem->copy(
                $destination,
                ['name' => 'Test folder (copied)']
            );

            self::waitUntil(function () use ($uri) {
                return $this->getAndDecode($uri)->status == 'completed';
            });

            $this->assertRegExp(self::$uriRegex, $uri);
            $driveItems = $destination->getChildren(['top' => 2]);
            $this->assertCount(1, $driveItems);
            $driveItem = $driveItems[0];
            $this->assertDriveItemProxy($driveItem);
            $this->assertNotNull($driveItem->parentReference);
            $this->assertEquals($destination->id, $driveItem->parentReference->id);
            $this->assertEquals('Test folder (copied)', $driveItem->name);
        });
    }

    /**
     * @dataProvider provideLinkTypes
     */
    public function testCreateLinkToFileWhenNotExisting($type)
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_' . __FUNCTION__, function (DriveItemProxy $sandbox) use ($type) {
            $driveItem = $sandbox->upload(
                'Test file',
                'Test content',
                ['contentType' => 'text/plain']
            );

            $permission = $driveItem->createLink($type);
            $this->assertPermissionProxy($permission);
        });
    }

    /**
     * @dataProvider provideLinkTypes
     */
    public function testCreateLinkToFileWhenExisting($type)
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_' . __FUNCTION__, function (DriveItemProxy $sandbox) use ($type) {
            $driveItem = $sandbox->upload(
                'Test file',
                'Test content',
                ['contentType' => 'text/plain']
            );

            $driveItem->createLink($type);
            $permission = $driveItem->createLink($type);
            $this->assertPermissionProxy($permission);
        });
    }

    /**
     * @dataProvider provideLinkTypes
     */
    public function testCreateLinkToFolderWhenNotExisting($type)
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_' . __FUNCTION__, function (DriveItemProxy $sandbox) use ($type) {
            $driveItem  = $sandbox->createFolder('Test folder');
            $permission = $driveItem->createLink($type);
            $this->assertPermissionProxy($permission);
        });
    }

    /**
     * @dataProvider provideLinkTypes
     */
    public function testCreateLinkToFolderWhenExisting($type)
    {
        self::withOnedriveSandbox(self::$driveItem, __CLASS__ . '_' . __FUNCTION__, function (DriveItemProxy $sandbox) use ($type) {
            $driveItem = $sandbox->createFolder('Test folder');
            $driveItem->createLink($type);
            $permission = $driveItem->createLink($type);
            $this->assertPermissionProxy($permission);
        });
    }

    public function provideLinkTypes()
    {
        return [
            ['view'],
            ['edit'],
            ['embed'],
        ];
    }
}
