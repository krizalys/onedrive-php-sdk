<?php

namespace Test\Functional\Krizalys\Onedrive;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Krizalys\Onedrive\Client;
use Krizalys\Onedrive\DriveItem;
use Krizalys\Onedrive\File;
use Krizalys\Onedrive\Folder;
use Krizalys\Onedrive\StreamBackEnd;
use Symfony\Component\Process\Process;

/**
 * @group functional
 */
class KrizalysOnedriveTest extends \PHPUnit_Framework_TestCase
{
    const REDIRECT_URI_PORT = 7777;

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

        $client = new Client([
            'client_id'       => $config['CLIENT_ID'],
            'stream_back_end' => StreamBackEnd::TEMP,
        ]);

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

    public function testClientRenewAccessToken()
    {
        $before = clone self::$client->getState()->token;
        self::$client->renewAccessToken(self::$clientSecret);
        $after = clone self::$client->getState()->token;
        $this->assertNotEquals($before->obtained, $after->data->obtained);
        $this->assertNotEquals($before->data->access_token, $after->data->access_token);
        $this->assertNotEquals($before->data->refresh_token, $after->data->refresh_token);
    }

    public function testClientCreateFolder()
    {
        $root = self::$client->fetchDriveItem();

        $folder1 = self::$client->createFolder('Test folder #1', null, null);
        $this->assertInstanceOf(Folder::class, $folder1);
        $this->assertEquals('Test folder #1', $folder1->getName());

        $children = array_map(function (DriveItem $driveItem) {
            return $driveItem->getId();
        }, $root->fetchChildDriveItems());

        $this->assertContains($folder1->getId(), $children);

        $folder2 = self::$client->createFolder('Test folder #2', null, 'Test description folder #2');
        $this->assertInstanceOf(Folder::class, $folder2);
        $this->assertEquals('Test folder #2', $folder2->getName());

        $children = array_map(function (DriveItem $driveItem) {
            return $driveItem->getId();
        }, $root->fetchChildDriveItems());

        $this->assertContains($folder2->getId(), $children);
        $this->assertEquals('Test description folder #2', $folder2->getDescription());

        $folder3 = self::$client->createFolder('Test folder #3', $folder1->getId(), null);
        $this->assertInstanceOf(Folder::class, $folder3);
        $this->assertEquals('Test folder #3', $folder3->getName());
        $this->assertEquals($folder1->getId(), $folder3->getParentId());

        $children = array_map(function (DriveItem $driveItem) {
            return $driveItem->getId();
        }, $folder1->fetchChildDriveItems());

        $this->assertContains($folder3->getId(), $children);

        return [$folder1, $folder2, $folder3];
    }

    /**
     * @depends testClientCreateFolder
     */
    public function testClientCreateFile(array $arguments)
    {
        $folder1 = $arguments[0];
        $folder2 = $arguments[1];
        $folder3 = $arguments[2];

        // Test with a text file.
        $file1 = self::$client->createFile('Test file #1.txt', $folder1->getId(), 'Test content');
        $this->assertInstanceOf(File::class, $file1);
        $this->assertEquals('Test file #1.txt', $file1->getName());
        $this->assertEquals($folder1->getId(), $file1->getParentId());

        $children = array_map(function (DriveItem $driveItem) {
            return $driveItem->getId();
        }, $folder1->fetchChildDriveItems());

        $this->assertContains($file1->getId(), $children);

        $file1 = self::$client->fetchDriveItem($file1->getId());
        $this->assertInstanceOf(File::class, $file1);
        $this->assertEquals('Test file #1.txt', $file1->getName());
        $this->assertEquals($folder1->getId(), $file1->getParentId());

        $actual = $file1->fetchContent();
        $this->assertEquals('Test content', $actual);

        // Test with a binary file.
        $file2 = self::$client->createFile('Test file #2.png', $folder2->getId(), base64_decode(self::PHP_LOGO_PNG_BASE64));
        $this->assertInstanceOf(File::class, $file2);
        $this->assertEquals('Test file #2.png', $file2->getName());
        $this->assertEquals($folder2->getId(), $file2->getParentId());

        $children = array_map(function (DriveItem $driveItem) {
            return $driveItem->getId();
        }, $folder2->fetchChildDriveItems());

        $this->assertContains($file2->getId(), $children);

        return [$folder1, $folder2, $folder3, $file1];
    }

    public function testClientFetchDriveItem()
    {
        $root = self::$client->fetchRoot();
        $item = self::$client->fetchDriveItem($root->getId());
        $this->assertInstanceOf(Folder::class, $item);
        $this->assertEquals('SkyDrive', $item->getName());
        $this->assertEquals('', $item->getParentId());
    }

    public function testClientFetchRoot()
    {
        $root = self::$client->fetchRoot();
        $this->assertInstanceOf(Folder::class, $root);
        $this->assertEquals('SkyDrive', $root->getName());
        $this->assertEquals('', $root->getParentId());
    }

    public function testClientFetchCameraRoll()
    {
        $pics       = self::$client->fetchPics();
        $cameraRoll = self::$client->fetchCameraRoll();
        $this->assertInstanceOf(Folder::class, $cameraRoll);
        $this->assertEquals('Camera Roll', $cameraRoll->getName());

        $children = array_map(function (DriveItem $driveItem) {
            return $driveItem->getName();
        }, $pics->fetchChildDriveItems());

        $this->assertContains($cameraRoll->getName(), $children);
    }

    public function testClientFetchDocs()
    {
        $root = self::$client->fetchDriveItem();
        $docs = self::$client->fetchDocs();
        $this->assertInstanceOf(Folder::class, $docs);
        $this->assertEquals('Documents', $docs->getName());

        $children = array_map(function (DriveItem $driveItem) {
            return $driveItem->getName();
        }, $root->fetchChildDriveItems());

        $this->assertContains($docs->getName(), $children);
    }

    public function testClientFetchPics()
    {
        $root = self::$client->fetchDriveItem();
        $pics = self::$client->fetchPics();
        $this->assertInstanceOf(Folder::class, $pics);
        $this->assertEquals('Pictures', $pics->getName());

        $children = array_map(function (DriveItem $driveItem) {
            return $driveItem->getName();
        }, $root->fetchChildDriveItems());

        $this->assertContains($pics->getName(), $children);
    }

    public function testClientFetchPublicDocs()
    {
        $root       = self::$client->fetchDriveItem();
        $publicDocs = self::$client->fetchPublicDocs();
        $this->assertInstanceOf(Folder::class, $publicDocs);
        $this->assertEquals('Public', $publicDocs->getName());

        $children = array_map(function (DriveItem $driveItem) {
            return $driveItem->getName();
        }, $root->fetchChildDriveItems());

        $this->assertContains($publicDocs->getName(), $children);
    }

    public function testClientFetchProperties()
    {
        $root       = self::$client->fetchDriveItem();
        $properties = self::$client->fetchProperties($root->getId());
        $this->assertInternalType('object', $properties);
        $this->assertInternalType('string', $properties->id);
        $this->assertInternalType('object', $properties->from);
        $this->assertNull($properties->from->name);
        $this->assertNull($properties->from->id);
        $this->assertEquals('SkyDrive', $properties->name);
        $this->assertEquals('', $properties->description);
        $this->assertNull($properties->parent_id);
        $this->assertGreaterThanOrEqual(0, $properties->size);
        $this->assertRegExp(self::URI_REGEX, $properties->upload_location);
        $this->assertGreaterThanOrEqual(0, $properties->comments_count);
        $this->assertFalse($properties->comments_enabled);
        $this->assertFalse($properties->is_embeddable);
        $this->assertGreaterThanOrEqual(0, $properties->count);
        $this->assertRegExp(self::URI_REGEX, $properties->link);
        $this->assertContains($properties->type, ['folder']);
        $this->assertInternalType('object', $properties->shared_with);
        $this->assertEquals('Just me', $properties->shared_with->access);
        $this->assertNull($properties->created_time);
        $this->assertRegExp(self::DATETIME_REGEX, $properties->updated_time);
        $this->assertRegExp(self::DATETIME_REGEX, $properties->client_updated_time);
    }

    public function testClientFetchDriveItems()
    {
        $root  = self::$client->fetchDriveItem();
        $items = self::$client->fetchDriveItems($root->getId());
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

    public function testClientUpdateDriveItem()
    {
        $item = self::$client->createFolder('Test folder', null, null);

        self::$client->updateDriveItem(
            $item->getId(),
            [
                'name'        => 'Test folder (renamed)',
                'description' => 'Test description folder',
            ],
            false
        );

        $item = self::$client->fetchDriveItem($item->getId());
        $this->assertEquals('Test folder (renamed)', $item->getName());
        $this->assertEquals('Test description folder', $item->getDescription());
        self::$client->deleteDriveItem($item->getId());
    }

    /**
     * @depends testClientCreateFile
     */
    public function testClientMoveDriveItem(array $arguments)
    {
        $folder1 = $arguments[0];
        $folder2 = $arguments[1];
        $folder3 = $arguments[2];
        $file1   = $arguments[3];

        $root = self::$client->fetchDriveItem();

        self::$client->moveDriveItem($file1->getId(), $folder2->getId());

        $children = array_map(function (DriveItem $driveItem) {
            return $driveItem->getId();
        }, $folder1->fetchChildDriveItems());

        $this->assertFalse(in_array($file1->getId(), $children));

        $children = array_map(function (DriveItem $driveItem) {
            return $driveItem->getId();
        }, $folder2->fetchChildDriveItems());

        $this->assertContains($file1->getId(), $children);

        $file1 = self::$client->fetchDriveItem($file1->getId());
        $this->assertEquals($folder2->getId(), $file1->getParentId());

        return [$folder1, $folder2, $folder3, $file1];
    }

    /**
     * @depends testClientMoveDriveItem
     */
    public function testClientCopyDriveItem(array $arguments)
    {
        $folder1 = $arguments[0];
        $folder2 = $arguments[1];
        $folder3 = $arguments[2];
        $file1   = $arguments[3];

        $root = self::$client->fetchDriveItem();

        self::$client->copyFile($file1->getId(), $folder1->getId());

        $children = array_map(function (DriveItem $driveItem) {
            return $driveItem->getId();
        }, $folder2->fetchChildDriveItems());

        $this->assertContains($file1->getId(), $children);

        $children = array_map(function (DriveItem $driveItem) {
            return $driveItem->getName();
        }, $folder1->fetchChildDriveItems());

        $this->assertContains('Test file #1.txt', $children);

        return [$folder1, $folder2, $folder3];
    }

    /**
     * @depends testClientCopyDriveItem
     */
    public function testClientDeleteDriveItem(array $arguments)
    {
        $folder1 = $arguments[0];
        $folder2 = $arguments[1];
        $folder3 = $arguments[2];

        $root = self::$client->fetchDriveItem();

        self::$client->deleteDriveItem($folder3->getId());

        $children = array_map(function (DriveItem $driveItem) {
            return $driveItem->getId();
        }, $folder1->fetchChildDriveItems());

        $this->assertFalse(in_array($folder3->getId(), $children));

        self::$client->deleteDriveItem($folder1->getId());

        $children = array_map(function (DriveItem $driveItem) {
            return $driveItem->getId();
        }, $root->fetchChildDriveItems());

        $this->assertFalse(in_array($folder1->getId(), $children));

        self::$client->deleteDriveItem($folder2->getId());

        $children = array_map(function (DriveItem $driveItem) {
            return $driveItem->getId();
        }, $root->fetchChildDriveItems());

        $this->assertFalse(in_array($folder2->getId(), $children));
    }

    public function testClientFetchQuota()
    {
        $quota = self::$client->fetchQuota();
        $this->assertInternalType('object', $quota);
        $this->assertGreaterThanOrEqual(0, $quota->quota);
        $this->assertGreaterThanOrEqual(0, $quota->available);
    }

    public function testClientFetchAccountInfo()
    {
        $accountInfo = self::$client->fetchAccountInfo();
        $this->assertInternalType('object', $accountInfo);
        $this->assertInternalType('string', $accountInfo->id);
        $this->assertInternalType('string', $accountInfo->first_name);
        $this->assertInternalType('string', $accountInfo->last_name);
        $firstName = $accountInfo->first_name;
        $lastName  = $accountInfo->last_name;
        $name      = "$firstName $lastName";
        $this->assertEquals($name, $accountInfo->name);
        $this->assertNull($accountInfo->gender);
        $this->assertContains($accountInfo->locale, ['en_US']);
    }

    public function testClientFetchRecentDocs()
    {
        $recentDocs = self::$client->fetchRecentDocs();
        $this->assertInternalType('object', $recentDocs);
        $this->assertInternalType('array', $recentDocs->data);

        foreach ($recentDocs->data as $data) {
            $this->assertInternalType('object', $data);
        }
    }

    public function testClientFetchShared()
    {
        $shared = self::$client->fetchShared();
        $this->assertInternalType('object', $shared);
        $this->assertInternalType('array', $shared->data);

        foreach ($shared->data as $data) {
            $this->assertInternalType('object', $data);
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
            'wl.skydrive_update',
            'wl.contacts_photos',
            'wl.contacts_skydrive',
            'wl.offline_access',
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
