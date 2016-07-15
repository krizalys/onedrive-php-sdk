<?php

namespace Test\Integration\Krizalys;

use Krizalys\Onedrive\Client;

/**
 * @group integration
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    const PORT = 7777;

    private static $client;

    private static function getAuthenticationCode(Client $client)
    {
        // TODO: Figure out why the callback URL (passed as the second
        // parameter) does not seem to matter here, but passing a non-empty
        // string causes the authentication process to not be initiated.
        $url = $client->getLoginUrl(array('wl.skydrive_update'), '');

        echo "Integration test suite started.\n\nPlease sign into your OneDrive account from this page and grant to the app all\nthe privileges requested:\n\n\t$url\n\nThis process will then resume, do not interrupt it.\n";

        $server = @socket_create_listen(self::PORT, 1);

        if (false === $server) {
            $message = socket_strerror(socket_last_error());
            throw new \Exception($message);
        }

        $socketRemote = @socket_accept($server);

        if (false === $socketRemote) {
            $message = socket_strerror(socket_last_error());
            socket_close($server);
            throw new \Exception($message);
        }

        $buffer = @socket_read($socketRemote, 4096, PHP_BINARY_READ);

        if (false === $buffer) {
            $message = socket_strerror(socket_last_error());
            socket_close($socketRemote);
            socket_close($server);
            throw new \Exception($message);
        }

        $size = @socket_write($socketRemote, implode("\r\n", array(
            'HTTP/1.1 200 OK',
            'Content-Type: text/html; charset=utf-8',
            '',
            '<!DOCTYPE html><h1>Thank you</h1><p>The integration test suite started running. You can close this window.</p>',
        )));

        if (false === $size) {
            $message = socket_strerror(socket_last_error());
            socket_close($socketRemote);
            socket_close($server);
            throw new \Exception($message);
        }

        socket_close($socketRemote);
        socket_close($server);
        list($headers, $body) = explode("\r\n\r\n", $buffer);

        $headers = explode("\r\n", $headers);
        $request = $headers[0];

        if (1 !== preg_match('/^GET\s+(.+)\s+HTTP\/1\.1$/', $request, $matches)) {
            throw new \Exception('Unsupported HTTP request format');
        }

        $url        = $matches[1];
        $components = parse_url($url);
        $query      = $components['query'];
        $params     = explode('&', $query);
        $query      = array();

        array_map(function ($param) use (&$query) {
            list($key, $value) = explode('=', $param);
            $query[$key] = $value;
        }, $params);

        if (!array_key_exists('code', $query)) {
            throw new \Exception('Code is missing from the request. Did you log in successfully and granted all the privileges requested?');
        }

        return $query['code'];
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $config = sprintf('%s/config.php', __DIR__);

        if (!file_exists($config)) {
            throw new \Exception("Configuration file not found. Please create a $config file from the sample provided.");
        }

        $config = require $config;
        $url    = sprintf('http://localhost:%u/', self::PORT);

        $client = new Client(array(
            'client_id' => $config['CLIENT_ID'],
        ), $url);

        try {
            $code = self::getAuthenticationCode($client);
        } catch (\Exception $e) {
            die($e->getMessage() . "\n");
        }

        $client->obtainAccessToken($config['SECRET'], $code);
        self::$client = $client;
    }

    public function testCreateFolder()
    {
        $folder1 = self::$client->createFolder('Test folder #1');
        $this->assertNotNull($folder1);

        $folder2 = self::$client->createFolder('Test folder #2');
        $this->assertNotNull($folder2);

        return array($folder1, $folder2);
    }

    /**
     * @depends testCreateFolder
     */
    public function testCreateFile($arguments)
    {
        $folder1 = $arguments[0];
        $folder2 = $arguments[1];

        $file1 = self::$client->createFile('Test file', $folder1->getId(), 'Test content');
        $this->assertNotNull($file1);

        $file1 = self::$client->fetchObject($file1->getId());
        $this->assertNotNull($file1);

        $actual = $file1->fetchContent();
        $this->assertEquals('Test content', $actual);

        return array($folder1, $folder2, $file1);
    }

    /**
     * @depends testCreateFile
     */
    public function testMoveObject($arguments)
    {
        $folder1 = $arguments[0];
        $folder2 = $arguments[1];
        $file1   = $arguments[2];

        self::$client->moveObject($file1->getId(), $folder2->getId());

        return array($folder1, $folder2, $file1);
    }

    /**
     * @depends testMoveObject
     */
    public function testCopyObject($arguments)
    {
        $folder1 = $arguments[0];
        $folder2 = $arguments[1];
        $file1   = $arguments[2];

        self::$client->copyFile($file1->getId(), $folder1->getId());

        return array($folder1, $folder2);
    }

    /**
     * @depends testCopyObject
     */
    public function testDeleteObject($arguments)
    {
        $folder1 = $arguments[0];
        $folder2 = $arguments[1];

        self::$client->deleteObject($folder1->getId());

        self::$client->deleteObject($folder2->getId());

        $this->assertTrue(true);
    }
}
