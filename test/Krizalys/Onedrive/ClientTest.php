<?php

namespace Test\Krizalys\Onedrive
{
    use Krizalys\Onedrive\Client;
    use Mockery as m;

    class ClientTest extends \PHPUnit_Framework_TestCase
    {
        public static $functions;

        public static function mockTokenData($prefix = 'OlD')
        {
            return (object) array(
                'token_type'           => 'bearer',
                'expires_in'           => 3600,
                'scope'                => 'wl.signin wl.basic wl.contacts_skydrive wl.skydrive_update wl.offline_access',
                'access_token'         => "$prefix/AcCeSs+ToKeN",
                'refresh_token'        => "$prefix!ReFrEsH*ToKeN",
                'authentication_token' => "$prefix.AuThEnTiCaTiOn_ToKeN",
                'user_id'              => 'ffffffffffffffffffffffffffffffff',
            );
        }

        public function setUp()
        {
            parent::setUp();
            self::$functions = m::mock();
        }

        private function mockClientId()
        {
            return '9999999999999999';
        }

        private function mockClientSecret()
        {
            return 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
        }

        private function mockCurlSetopt(
            $return           = true,
            array &$arguments = array()
        ) {
            self::$functions
                ->shouldReceive('curl_setopt')
                ->andReturnUsing(function ($ch, $option, $value) use ($return, &$arguments) {
                    $arguments = array(
                        'ch'     => $ch,
                        'option' => $option,
                        'value'  => $value,
                    );

                    return $return;
                });
        }

        private function mockCurlSetoptArray(
            $return           = true,
            array &$arguments = array()
        ) {
            self::$functions
                ->shouldReceive('curl_setopt_array')
                ->andReturnUsing(function ($ch, $options) use ($return, &$arguments) {
                    $arguments = array(
                        'ch'      => $ch,
                        'options' => $options,
                    );

                    return $return;
                });
        }

        private function mockCurlExec(
            $return,
            array &$arguments = array()
        ) {
            self::$functions
                ->shouldReceive('curl_exec')
                ->andReturnUsing(function ($ch) use ($return, &$arguments) {
                    $arguments = array('ch' => $ch);

                    return $return;
                });
        }

        private function mockCurlInfo(
            $return           = array('content_type' => 'application/json'),
            array &$arguments = array()
        ) {
            self::$functions
                ->shouldReceive('curl_getinfo')
                ->andReturnUsing(function ($curl, $opt) use ($return, &$arguments) {
                    $arguments = array(
                        'ch'  => $curl,
                        'opt' => $opt,
                    );

                    return $return;
                });
        }

        public function testGetLogInUrl()
        {
            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => null,
                ),
            ));

            $scopes = array(
                'test_scope_1',
                'test_scope_2',
            );

            $opts = array(
                'unused'   => 'useless',
                'reserved' => 'future',
            );

            $actual = $client->getLogInUrl($scopes, 'http://te.st/callback', $opts);
            $this->assertEquals('https://login.live.com/oauth20_authorize.srf?client_id=9999999999999999&scope=test_scope_1%2Ctest_scope_2&response_type=code&redirect_uri=http%3A%2F%2Fte.st%2Fcallback&display=popup&locale=en', $actual);
        }

        public function testGetTokenExpire()
        {
            self::$functions
                ->shouldReceive('time')
                ->andReturn(strtotime('1999-01-01T00:00:01Z'));

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01T00:00:00Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $expected = 3599;
            $actual   = $client->getTokenExpire();
            $this->assertEquals($expected, $actual);
        }

        public function provideGetAccessTokenStatus()
        {
            return array(
                'Fresh token' => array(
                    'time'     => strtotime('1999-01-01T00:58:59Z'),
                    'expected' => 1,
                ),

                'Expiring token' => array(
                    'time'     => strtotime('1999-01-01T00:59:00Z'),
                    'expected' => -1,
                ),

                'Expired token' => array(
                    'time'     => strtotime('1999-01-01T01:00:00Z'),
                    'expected' => -2,
                ),
            );
        }

        /**
         * @dataProvider provideGetAccessTokenStatus
         */
        public function testGetAccessTokenStatus($time, $expected)
        {
            self::$functions
                ->shouldReceive('time')
                ->andReturn($time);

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01T00:00:00Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $actual = $client->getAccessTokenStatus();
            $this->assertEquals($expected, $actual);
        }

        public function testObtainAccessToken()
        {
            $this->mockCurlSetoptArray();

            self::$functions
                ->shouldReceive('time')
                ->andReturn(strtotime('1999-01-01Z'));

            $this->mockCurlExec(json_encode(self::mockTokenData('NeW')));

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => 'http://te.st/callback',
                    'token'        => null,
                ),
            ));

            $secret = $this->mockClientSecret();
            $client->obtainAccessToken($secret, 'X99ffffff-ffff-ffff-ffff-ffffffffffff');
            $actual = $client->getState();

            $this->assertEquals((object) array(
                'redirect_uri' => null,
                'token'        => (object) array(
                    'obtained' => strtotime('1999-01-01Z'),
                    'data'     => (object) array(
                        'token_type'           => 'bearer',
                        'expires_in'           => 3600,
                        'scope'                => 'wl.signin wl.basic wl.contacts_skydrive wl.skydrive_update wl.offline_access',
                        'access_token'         => 'NeW/AcCeSs+ToKeN',
                        'refresh_token'        => 'NeW!ReFrEsH*ToKeN',
                        'authentication_token' => 'NeW.AuThEnTiCaTiOn_ToKeN',
                        'user_id'              => 'ffffffffffffffffffffffffffffffff',
                    ),
                ),
            ), $actual);
        }

        public function testRenewAccessToken()
        {
            $this->mockCurlSetoptArray();

            self::$functions
                ->shouldReceive('time')
                ->andReturn(strtotime('1999-12-31Z'));

            $this->mockCurlExec(json_encode(self::mockTokenData('NeW')));

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $secret = $this->mockClientSecret();
            $client->renewAccessToken($secret);
            $actual = $client->getState();

            $this->assertEquals((object) array(
                'redirect_uri' => null,
                'token'        => (object) array(
                    'obtained' => strtotime('1999-12-31Z'),
                    'data'     => (object) array(
                        'token_type'           => 'bearer',
                        'expires_in'           => 3600,
                        'scope'                => 'wl.signin wl.basic wl.contacts_skydrive wl.skydrive_update wl.offline_access',
                        'access_token'         => 'NeW/AcCeSs+ToKeN',
                        'refresh_token'        => 'NeW!ReFrEsH*ToKeN',
                        'authentication_token' => 'NeW.AuThEnTiCaTiOn_ToKeN',
                        'user_id'              => 'ffffffffffffffffffffffffffffffff',
                    ),
                ),
            ), $actual);
        }

        public function testApiGet()
        {
            $this->mockCurlSetoptArray();
            $this->mockCurlSetopt();

            $this->mockCurlExec(json_encode((object) array(
                'key' => 'value',
            )));

            $this->mockCurlInfo();

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $actual = $client->apiGet('/path/to/resource');

            $this->assertEquals((object) array(
                'key' => 'value',
            ), $actual);
        }

        public function testApiPost()
        {
            $this->mockCurlSetoptArray();

            $this->mockCurlExec(json_encode((object) array(
                'output_key' => 'output_value',
            )));

            $this->mockCurlInfo();

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $actual = $client->apiPost('/path/to/resource', array(
                'input_key' => 'input_value',
            ));

            $this->assertEquals((object) array(
                'output_key' => 'output_value',
            ), $actual);
        }

        public function testApiPut()
        {
            $this->mockCurlSetoptArray();

            $this->mockCurlExec(json_encode((object) array(
                'key' => 'value',
            )));

            $this->mockCurlInfo();

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $stream = null;
            $actual = $client->apiPut('/path/to/resource', $stream, 'text/plain');

            $this->assertEquals((object) array(
                'key' => 'value',
            ), $actual);
        }

        public function testApiDelete()
        {
            $this->mockCurlSetoptArray();

            $this->mockCurlExec(json_encode((object) array(
                'key' => 'value',
            )));

            $this->mockCurlInfo();

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $actual = $client->apiDelete('/path/to/resource');

            $this->assertEquals((object) array(
                'key' => 'value',
            ), $actual);
        }

        public function testApiMove()
        {
            $this->mockCurlSetoptArray();

            $this->mockCurlExec(json_encode((object) array(
                'output_key' => 'output_value',
            )));

            $this->mockCurlInfo();

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $actual = $client->apiMove('/path/to/resource', array(
                'input_key' => 'input_value',
            ));

            $this->assertEquals((object) array(
                'output_key' => 'output_value',
            ), $actual);
        }

        public function testApiCopy()
        {
            $this->mockCurlSetoptArray();

            $this->mockCurlExec(json_encode((object) array(
                'output_key' => 'output_value',
            )));

            $this->mockCurlInfo();

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $actual = $client->apiCopy('/path/to/resource', array(
                'input_key' => 'input_value',
            ));

            $this->assertEquals((object) array(
                'output_key' => 'output_value',
            ), $actual);
        }

        public function provideCreateFolderUrl()
        {
            return array(
                'Parent omitted' => array(
                    'name'        => 'test-folder',
                    'parentId'    => null,
                    'description' => 'Some test description',
                    'expected'    => 'https://apis.live.net/v5.0/me/skydrive',
                ),

                'Parent given' => array(
                    'name'        => 'test-folder',
                    'parentId'    => 'path/to/parent',
                    'description' => 'Some test description',
                    'expected'    => 'https://apis.live.net/v5.0/path/to/parent',
                ),
            );
        }

        /**
         * @dataProvider provideCreateFolderUrl
         */
        public function testCreateFolderUrl($name, $parentId, $description, $expected)
        {
            $arguments = array();
            $this->mockCurlSetoptArray(true, $arguments);

            $this->mockCurlExec(json_encode((object) array(
                'id' => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
            )));

            $this->mockCurlInfo();

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $client->createFolder($name, $parentId, $description);
            $actual = $arguments['options'][CURLOPT_URL];
            $this->assertEquals($expected, $actual);
        }

        public function provideCreateFileUrl()
        {
            return array(
                'Parent omitted, OVERWRITE_ALWAYS' => array(
                    'name'       => 'test-file.txt',
                    'parentId'   => null,
                    'content'    => 'Some test content',
                    'overwrite'  => Client::OVERWRITE_ALWAYS,
                    'temp'       => false,
                    'expected'   => 'https://apis.live.net/v5.0/me/skydrive/files/test-file.txt?overwrite=true',
                ),

                'Parent given, OVERWRITE_ALWAYS' => array(
                    'name'      => 'test-file.txt',
                    'parentId'  => 'path/to/parent',
                    'content'   => 'Some test content',
                    'overwrite' => Client::OVERWRITE_ALWAYS,
                    'temp'      => false,
                    'expected'  => 'https://apis.live.net/v5.0/path/to/parent/files/test-file.txt?overwrite=true',
                ),

                'Parent omitted, OVERWRITE_NEVER' => array(
                    'name'       => 'test-file.txt',
                    'parentId'   => null,
                    'content'    => 'Some test content',
                    'overwrite'  => Client::OVERWRITE_NEVER,
                    'temp'       => false,
                    'expected'   => 'https://apis.live.net/v5.0/me/skydrive/files/test-file.txt?overwrite=false',
                ),

                'Parent given, OVERWRITE_NEVER' => array(
                    'name'      => 'test-file.txt',
                    'parentId'  => 'path/to/parent',
                    'content'   => 'Some test content',
                    'overwrite' => Client::OVERWRITE_NEVER,
                    'temp'      => false,
                    'expected'  => 'https://apis.live.net/v5.0/path/to/parent/files/test-file.txt?overwrite=false',
                ),

                'Parent omitted, OVERWRITE_NEVER' => array(
                    'name'       => 'test-file.txt',
                    'parentId'   => null,
                    'content'    => 'Some test content',
                    'overwrite'  => Client::OVERWRITE_RENAME,
                    'temp'       => false,
                    'expected'   => 'https://apis.live.net/v5.0/me/skydrive/files/test-file.txt?overwrite=ChooseNewName',
                ),

                'Parent given, OVERWRITE_NEVER' => array(
                    'name'      => 'test-file.txt',
                    'parentId'  => 'path/to/parent',
                    'content'   => 'Some test content',
                    'overwrite' => Client::OVERWRITE_RENAME,
                    'temp'      => false,
                    'expected'  => 'https://apis.live.net/v5.0/path/to/parent/files/test-file.txt?overwrite=ChooseNewName',
                ),
            );
        }

        /**
         * @dataProvider provideCreateFileUrl
         */
        public function testCreateFileUrl(
            $name,
            $parentId,
            $content,
            $overwrite,
            $temp,
            $expected
        ) {
            $arguments = array();
            $this->mockCurlSetoptArray(true, $arguments);

            $this->mockCurlExec(json_encode((object) array(
                'id' => 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
            )));

            $this->mockCurlInfo();

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $client->createFile($name, $parentId, $content, $overwrite, $temp);
            $actual = $arguments['options'][CURLOPT_URL];
            $this->assertEquals($expected, $actual);
        }

        public function provideFetchObjectType()
        {
            return array(
                'File' => array(
                    'type'     => 'file',
                    'expected' => 'File',
                ),

                'Folder' => array(
                    'type'     => 'folder',
                    'expected' => 'Folder',
                ),

                'Album' => array(
                    'type'     => 'album',
                    'expected' => 'Folder',
                ),
            );
        }

        /**
         * @dataProvider provideFetchObjectType
         */
        public function testFetchObjectType($type, $expected)
        {
            $this->mockCurlSetoptArray();
            $this->mockCurlSetopt();

            $this->mockCurlExec(json_encode((object) array(
                'type' => $type,
            )));

            $this->mockCurlInfo();

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $object = $client->fetchObject('some-resource');
            $actual = get_class($object);
            $this->assertEquals("Krizalys\Onedrive\\$expected", $actual);
        }

        public function testFetchRootUrl()
        {
            $this->mockCurlSetoptArray();

            $arguments = array();
            $this->mockCurlSetopt(true, $arguments);

            $this->mockCurlExec(json_encode((object) array(
                'id'   => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                'type' => 'folder',
            )));

            $this->mockCurlInfo();

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $client->fetchRoot();
            $actual = $arguments['value'];
            $this->assertEquals('https://apis.live.net/v5.0/me/skydrive?access_token=OlD%2FAcCeSs%2BToKeN', $actual);
        }

        public function testFetchCameraRollUrl()
        {
            $this->mockCurlSetoptArray();

            $arguments = array();
            $this->mockCurlSetopt(true, $arguments);

            $this->mockCurlExec(json_encode((object) array(
                'id'   => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                'type' => 'folder',
            )));

            $this->mockCurlInfo();

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $client->fetchCameraRoll();
            $actual = $arguments['value'];
            $this->assertEquals('https://apis.live.net/v5.0/me/skydrive/camera_roll?access_token=OlD%2FAcCeSs%2BToKeN', $actual);
        }

        public function testFetchDocsUrl()
        {
            $this->mockCurlSetoptArray();

            $arguments = array();
            $this->mockCurlSetopt(true, $arguments);

            $this->mockCurlExec(json_encode((object) array(
                'id'   => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                'type' => 'folder',
            )));

            $this->mockCurlInfo();

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $client->fetchDocs();
            $actual = $arguments['value'];
            $this->assertEquals('https://apis.live.net/v5.0/me/skydrive/my_documents?access_token=OlD%2FAcCeSs%2BToKeN', $actual);
        }

        public function testFetchCameraPicsUrl()
        {
            $this->mockCurlSetoptArray();

            $arguments = array();
            $this->mockCurlSetopt(true, $arguments);

            $this->mockCurlExec(json_encode((object) array(
                'id'   => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                'type' => 'folder',
            )));

            $this->mockCurlInfo();

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $client->fetchPics();
            $actual = $arguments['value'];
            $this->assertEquals('https://apis.live.net/v5.0/me/skydrive/my_photos?access_token=OlD%2FAcCeSs%2BToKeN', $actual);
        }

        public function testFetchPublicDocsUrl()
        {
            $this->mockCurlSetoptArray();

            $arguments = array();
            $this->mockCurlSetopt(true, $arguments);

            $this->mockCurlExec(json_encode((object) array(
                'id'   => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                'type' => 'folder',
            )));

            $this->mockCurlInfo();

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $client->fetchPublicDocs();
            $actual = $arguments['value'];
            $this->assertEquals('https://apis.live.net/v5.0/me/skydrive/public_documents?access_token=OlD%2FAcCeSs%2BToKeN', $actual);
        }

        public function provideFetchPropertiesUrl()
        {
            return array(
                'Null object ID' => array(
                    'objectId' => null,
                    'expected' => 'https://apis.live.net/v5.0/me/skydrive?access_token=OlD%2FAcCeSs%2BToKeN',
                ),

                'Non-null object ID' => array(
                    'objectId' => 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                    'expected' => 'https://apis.live.net/v5.0/file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123?access_token=OlD%2FAcCeSs%2BToKeN',
                ),
            );
        }

        /**
         * @dataProvider provideFetchPropertiesUrl
         */
        public function testFetchPropertiesUrl($objectId, $expected)
        {
            $this->mockCurlSetoptArray();

            $arguments = array();
            $this->mockCurlSetopt(true, $arguments);
            $this->mockCurlExec(json_encode((object) array()));
            $this->mockCurlInfo();

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $client->fetchProperties($objectId);
            $actual = $arguments['value'];
            $this->assertEquals($expected, $actual);
        }

        public function provideFetchObjectsUrl()
        {
            return array(
                'Null object ID' => array(
                    'objectId' => null,
                    'expected' => 'https://apis.live.net/v5.0/me/skydrive/files?access_token=OlD%2FAcCeSs%2BToKeN',
                ),

                'Non-null object ID' => array(
                    'objectId' => 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                    'expected' => 'https://apis.live.net/v5.0/file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123/files?access_token=OlD%2FAcCeSs%2BToKeN',
                ),
            );
        }

        /**
         * @dataProvider provideFetchObjectsUrl
         */
        public function testFetchObjectsUrl($objectId, $expected)
        {
            $this->mockCurlSetoptArray();

            $arguments = array();
            $this->mockCurlSetopt(true, $arguments);

            $this->mockCurlExec(json_encode((object) array(
                'data' => array(),
            )));

            $this->mockCurlInfo();

            $client = new Client(array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $client->fetchObjects($objectId);
            $actual = $arguments['value'];
            $this->assertEquals($expected, $actual);
        }
    }
}

/**
 * Global function mocks.
 */
namespace Krizalys\Onedrive
{
    use Test\Krizalys\Onedrive\ClientTest;

    function time()
    {
        return ClientTest::$functions->time();
    }

    function fstat()
    {
        return array(
            /* Size */ 7 => 123,
        );
    }

    function curl_init()
    {
        // Nothing for now (return null).
    }

    function curl_setopt($ch, $option, $value)
    {
        return ClientTest::$functions->curl_setopt($ch, $option, $value);
    }

    function curl_setopt_array($ch, array $options)
    {
        return ClientTest::$functions->curl_setopt_array($ch, $options);
    }

    function curl_exec($ch)
    {
        return ClientTest::$functions->curl_exec($ch);
    }

    function curl_getinfo($ch, $opt = 0)
    {
        return ClientTest::$functions->curl_getinfo($ch, $opt);
    }
}
