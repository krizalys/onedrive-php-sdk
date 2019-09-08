<?php

namespace Test\Functional\Krizalys\Onedrive\Proxy;

use Krizalys\Onedrive\Proxy\DriveItemProxy;
use Test\Functional\Krizalys\Onedrive\Traits\AssertionsTrait;
use Test\Functional\Krizalys\Onedrive\Traits\ClientFactoryTrait;
use Test\Functional\Krizalys\Onedrive\Traits\ConfigurationTrait;
use Test\Functional\Krizalys\Onedrive\Traits\OnedriveSandboxTrait;

class DriveProxyTest extends \PHPUnit_Framework_TestCase
{
    use AssertionsTrait;
    use ClientFactoryTrait;
    use ConfigurationTrait;
    use OnedriveSandboxTrait;

    private static $drive;

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

        self::$drive = $client->getMyDrive();
    }

    public function testGetDriveItemById()
    {
        $root      = self::$drive->getRoot();
        $driveItem = self::$drive->getDriveItemById($root->id);
        $this->assertDriveItemProxy($driveItem);
    }

    public function testGetDriveItemByPath()
    {
        $root = self::$drive->getRoot();

        self::withOnedriveSandbox($root, __METHOD__, function (DriveItemProxy $sandbox) {
            $sandbox->upload(
                'Test file',
                'Test content',
                ['contentType' => 'text/plain']
            );

            $driveItem = self::$drive->getDriveItemByPath("/{$sandbox->name}/Test file");
            $this->assertDriveItemProxy($driveItem);
            $this->assertEquals('Test file', $driveItem->name);
        });
    }
}
