<?php

namespace Test\Functional\Krizalys\Onedrive;

use Krizalys\Onedrive\Constant\SpecialFolderName;
use Krizalys\Onedrive\Proxy\DriveItemProxy;
use PHPUnit\Framework\TestCase;
use Test\Functional\Krizalys\Onedrive\Traits\AssertionsTrait;
use Test\Functional\Krizalys\Onedrive\Traits\ClientFactoryTrait;
use Test\Functional\Krizalys\Onedrive\Traits\ConfigurationTrait;
use Test\Functional\Krizalys\Onedrive\Traits\OnedriveSandboxTrait;

class ClientTest extends TestCase
{
    use AssertionsTrait;
    use ClientFactoryTrait;
    use ConfigurationTrait;
    use OnedriveSandboxTrait;

    private static $client;

    private static $defaultDrive;

    private static $root;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $clientId = self::getConfig('CLIENT_ID');
        $username = self::getConfig('USERNAME');
        $password = self::getConfig('PASSWORD');
        $secret   = self::getConfig('SECRET');

        self::$client = self::createClient(
            $clientId,
            $username,
            $password,
            $secret
        );
    }

    public function testGetDrives()
    {
        $drives = self::$client->getDrives();
        $actual = count($drives);
        $this->assertGreaterThanOrEqual(1, $actual);

        foreach ($drives as $drive) {
            $this->assertDriveProxy($drive);
        }
    }

    public function testGetMyDrive()
    {
        $drive = self::$client->getMyDrive();
        $this->assertDriveProxy($drive);
        self::$defaultDrive = $drive;
    }

    public function testGetRoot()
    {
        $driveItem = self::$client->getRoot();
        $this->assertDriveItemProxy($driveItem);
        $this->assertNotNull($driveItem->parentReference);
        $this->assertNull($driveItem->parentReference->id);
        $this->assertNotNull($driveItem->parentReference->driveId);
        $this->assertNotNull($driveItem->parentReference->driveType);
        $this->assertNull($driveItem->parentReference->path);
        $this->assertRootProxy($driveItem->root);
        self::$root = $driveItem;
    }

    /**
     * @depends testGetMyDrive
     */
    public function testGetDriveById()
    {
        $drive = self::$client->getDriveById(self::$defaultDrive->id);
        $this->assertDriveProxy($drive);
    }

    /**
     * @depends testGetMyDrive
     */
    public function testGetDriveByUser()
    {
        $drive = self::$client->getDriveByUser(self::$defaultDrive->owner->user->id);
        $this->assertDriveProxy($drive);
    }

    /**
     * @depends testGetMyDrive
     */
    public function testGetDriveByGroup()
    {
        $drive = self::$client->getDriveByGroup(self::$defaultDrive->owner->user->id);

        if ($drive == $null) {
            $this->markTestSkipped('No drive by group found');
        }

        $this->assertDriveProxy($drive);
    }

    /**
     * @depends testGetMyDrive
     */
    public function testGetDriveBySite()
    {
        $drive = self::$client->getDriveBySite(self::$defaultDrive->owner->user->id);

        if ($drive == $null) {
            $this->markTestSkipped('No drive by site found');
        }

        $this->assertDriveProxy($drive);
    }

    /**
     * @depends testGetRoot
     */
    public function testGetDriveItemByIdWhenNotGivenDriveId()
    {
        self::withOnedriveSandbox(self::$root, __METHOD__, function (DriveItemProxy $sandbox) {
            $driveItem = $sandbox->upload(
                'Test file',
                'Test content',
                ['contentType' => 'text/plain']
            );

            $driveItem = self::$client->getDriveItemById($driveItem->id);
            $this->assertDriveItemProxy($driveItem);
        });
    }

    /**
     * @depends testGetRoot
     */
    public function testGetDriveItemByIdWhenGivenDriveId()
    {
        self::withOnedriveSandbox(self::$root, __METHOD__, function (DriveItemProxy $sandbox) {
            $driveItem = $sandbox->upload(
                'Test file',
                'Test content',
                ['contentType' => 'text/plain']
            );

            $driveItem = self::$client->getDriveItemById(
                $driveItem->parentReference->driveId,
                $driveItem->id
            );

            $this->assertDriveItemProxy($driveItem);
        });
    }

    /**
     * @depends testGetRoot
     */
    public function testGetDriveItemByPath()
    {
        self::withOnedriveSandbox(self::$root, __METHOD__, function (DriveItemProxy $sandbox) {
            $sandbox->upload(
                'Test file',
                'Test content',
                ['contentType' => 'text/plain']
            );

            $driveItem = self::$client->getDriveItemByPath("/{$sandbox->name}/Test file");
            $this->assertDriveItemProxy($driveItem);
            $this->assertEquals('Test file', $driveItem->name);
        });
    }

    /**
     * @dataProvider specialFolderProvider
     */
    public function testGetSpecialFolder($specialFolderName)
    {
        $driveItem = self::$client->getSpecialFolder($specialFolderName);
        $this->assertDriveItemProxy($driveItem);
        $this->assertNotNull($driveItem->parentReference);
        $this->assertNotNull($driveItem->parentReference->id);
        $this->assertNotNull($driveItem->parentReference->driveId);
        $this->assertNotNull($driveItem->parentReference->driveType);
        $this->assertNotNull($driveItem->parentReference->path);

        // For some reason, this special folder does not have a SpecialFolder
        // facet.
        if ($specialFolderName != SpecialFolderName::APP_ROOT) {
            $this->assertSpecialFolderProxy($driveItem->specialFolder);
        }
    }

    public function testGetShared()
    {
        $driveItems = self::$client->getShared();
        $this->assertInternalType('array', $driveItems);

        foreach ($driveItems as $driveItem) {
            $this->assertDriveItemProxy($driveItem);
        }
    }

    public function testGetRecent()
    {
        $driveItems = self::$client->getRecent();
        $this->assertInternalType('array', $driveItems);

        foreach ($driveItems as $driveItem) {
            $this->assertDriveItemProxy($driveItem);
        }
    }

    public function specialFolderProvider()
    {
        return [
            [SpecialFolderName::DOCUMENTS],
            [SpecialFolderName::PHOTOS],
            [SpecialFolderName::CAMERA_ROLL],
            [SpecialFolderName::APP_ROOT],
            [SpecialFolderName::MUSIC],
        ];
    }
}
