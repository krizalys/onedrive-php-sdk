<?php

namespace Test\Functional\Krizalys\Onedrive;

use Krizalys\Onedrive\Constant\SpecialFolderName;
use Krizalys\Onedrive\Proxy\DriveItemProxy;
use Test\Functional\Krizalys\Onedrive\OnedriveSandboxTrait;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    use AssertionsTrait;
    use ClientFactoryTrait;
    use ConfigurationTrait;
    use OnedriveSandboxTrait;

    private static $client;

    private static $drive;

    private static $driveItem;

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
        self::$drive = $drive;
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
        self::$driveItem = $driveItem;
    }

    /**
     * @depends testGetMyDrive
     */
    public function testGetDriveById()
    {
        $drive = self::$client->getDriveById(self::$drive->id);
        $this->assertDriveProxy($drive);
    }

    /**
     * @depends testGetMyDrive
     */
    public function testGetDriveByUser()
    {
        $drive = self::$client->getDriveByUser(self::$drive->owner->user->id);
        $this->assertDriveProxy($drive);
    }

    /**
     * @depends testGetMyDrive
     */
    public function testGetDriveByGroup()
    {
        $drive = self::$client->getDriveByGroup(self::$drive->owner->user->id);

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
        $drive = self::$client->getDriveBySite(self::$drive->owner->user->id);

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
        $driveItem = self::$client->getDriveItemById(self::$driveItem->id);
        $this->assertDriveItemProxy($driveItem);
    }

    /**
     * @depends testGetRoot
     */
    public function testGetDriveItemByIdWhenGivenDriveId()
    {
        $driveItem = self::$client->getDriveItemById(
            self::$driveItem->parentReference->driveId,
            self::$driveItem->id
        );

        $this->assertDriveItemProxy($driveItem);
    }

    /**
     * @depends testGetRoot
     */
    public function testGetDriveItemByPath()
    {
        self::withOnedriveSandbox(self::$driveItem, __METHOD__, function (DriveItemProxy $sandbox) {
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
