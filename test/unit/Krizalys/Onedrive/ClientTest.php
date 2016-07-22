<?php

namespace Test\Krizalys\Onedrive;

use Krizalys\Onedrive\Client;
use Krizalys\Onedrive\NameConflictBehavior;
use Krizalys\Onedrive\StreamBackEnd;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Test\Mock\GlobalNamespace;

class ClientTest extends MockeryTestCase
{
    public static $functions;

    private $client;

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

    protected function setUp()
    {
        parent::setUp();
        $this->client = $this->getClient();
    }

    private function getClient(array $options = array())
    {
        $options = array_merge(
            array(
                'client_id' => $this->mockClientId(),
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ),
            $options
        );

        return new Client($options);
    }

    private function mockClientId()
    {
        return '9999999999999999';
    }

    private function mockClientSecret()
    {
        return 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
    }

    public function testGetLogInUrlShouldReturnExpectedValue()
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

    public function testGetTokenExpireShouldReturnExpectedValue()
    {
        GlobalNamespace::reset(array(
            'time' => function ($expectation) {
                $expectation->andReturn(strtotime('1999-01-01T00:00:01Z'));
            },
        ));

        $expected = 3599;

        $actual = $this
            ->client
            ->getTokenExpire();

        $this->assertEquals($expected, $actual);
    }

    public function provideGetAccessTokenStatusShouldReturnExpectedValue()
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
     * @dataProvider provideGetAccessTokenStatusShouldReturnExpectedValue
     */
    public function testGetAccessTokenStatusShouldReturnExpectedValue(
        $time,
        $expected
    ) {
        GlobalNamespace::reset(array(
            'time' => function ($expectation) use ($time) {
                $expectation->andReturn($time);
            },
        ));

        $actual = $this
            ->client
            ->getAccessTokenStatus();

        $this->assertEquals($expected, $actual);
    }

    public function testObtainAccessTokenShouldSetExpectedState()
    {
        GlobalNamespace::reset(array(
            'time' => function ($expectation) {
                $expectation->andReturn(strtotime('1999-01-01Z'));
            },

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode(self::mockTokenData('NeW')));
            },
        ));

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

    public function testRenewAccessTokenShouldSetExpectedState()
    {
        GlobalNamespace::reset(array(
            'time' => function ($expectation) {
                $expectation->andReturn(strtotime('1999-12-31Z'));
            },

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode(self::mockTokenData('NeW')));
            },
        ));

        $secret = $this->mockClientSecret();
        $client = $this->client;
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

    public function testApiGetShouldReturnExpectedValue()
    {
        GlobalNamespace::reset(array(
            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'key' => 'value',
                )));
            },
        ));

        $actual = $this
            ->client
            ->apiGet('/path/to/resource');

        $this->assertEquals((object) array(
            'key' => 'value',
        ), $actual);
    }

    public function testApiPostShouldReturnExpectedValue()
    {
        GlobalNamespace::reset(array(
            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'output_key' => 'output_value',
                )));
            },
        ));

        $actual = $this
            ->client
            ->apiPost('/path/to/resource', array(
                'input_key' => 'input_value',
            ));

        $this->assertEquals((object) array(
            'output_key' => 'output_value',
        ), $actual);
    }

    public function testApiPutShouldReturnExpectedValue()
    {
        GlobalNamespace::reset(array(
            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'key' => 'value',
                )));
            },
        ));

        $stream = null;

        $actual = $this
            ->client
            ->apiPut('/path/to/resource', $stream, 'text/plain');

        $this->assertEquals((object) array(
            'key' => 'value',
        ), $actual);
    }

    public function testApiDeleteShouldReturnExpectedValue()
    {
        GlobalNamespace::reset(array(
            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'key' => 'value',
                )));
            },
        ));

        $actual = $this
            ->client
            ->apiDelete('/path/to/resource');

        $this->assertEquals((object) array(
            'key' => 'value',
        ), $actual);
    }

    public function testApiMoveShouldReturnExpectedValue()
    {
        GlobalNamespace::reset(array(
            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'output_key' => 'output_value',
                )));
            },
        ));

        $actual = $this
            ->client
            ->apiMove('/path/to/resource', array(
                'input_key' => 'input_value',
            ));

        $this->assertEquals((object) array(
            'output_key' => 'output_value',
        ), $actual);
    }

    public function testApiCopyShouldReturnExpectedValue()
    {
        GlobalNamespace::reset(array(
            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'output_key' => 'output_value',
                )));
            },
        ));

        $actual = $this
            ->client
            ->apiCopy('/path/to/resource', array(
                'input_key' => 'input_value',
            ));

        $this->assertEquals((object) array(
            'output_key' => 'output_value',
        ), $actual);
    }

    public function provideCreateFolderShouldCallOnceCurlSetoptArrayWithExpectedUrl()
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
     * @dataProvider provideCreateFolderShouldCallOnceCurlSetoptArrayWithExpectedUrl
     */
    public function testCreateFolderShouldCallOnceCurlSetoptArrayWithExpectedUrl(
        $name,
        $parentId,
        $description,
        $expected
    ) {
        GlobalNamespace::reset(array(
            'curl_setopt_array' => array(
                function ($expectation) {
                    $expectation
                        ->once()
                        ->andReturn(true);
                },
                function ($expectation) use ($expected) {
                    $expectation
                        ->once()
                        ->withArgs(function ($ch, $options) use ($expected) {
                            return array_key_exists(CURLOPT_URL, $options) && $options[CURLOPT_URL] == $expected;
                        });
                },
            ),

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'id' => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                )));
            },
        ));

        $this
            ->client
            ->createFolder($name, $parentId, $description);
    }

    public function provideCreateFileShouldCallOnceCurlSetoptArrayWithExpectedUrl()
    {
        return array(
            'Parent omitted, FAIL name conflict behavior' => array(
                'name'     => 'test-file.txt',
                'parentId' => null,
                'content'  => 'Some test content',
                'options'  => array('name_conflict_behavior' => NameConflictBehavior::FAIL),
                'expected' => 'https://apis.live.net/v5.0/me/skydrive/files/test-file.txt?overwrite=false',
            ),

            'Parent given, FAIL name conflict behavior' => array(
                'name'     => 'test-file.txt',
                'parentId' => 'path/to/parent',
                'content'  => 'Some test content',
                'options'  => array('name_conflict_behavior' => NameConflictBehavior::FAIL),
                'expected' => 'https://apis.live.net/v5.0/path/to/parent/files/test-file.txt?overwrite=false',
            ),

            'Parent omitted, RENAME name conflict behavior' => array(
                'name'     => 'test-file.txt',
                'parentId' => null,
                'content'  => 'Some test content',
                'options'  => array('name_conflict_behavior' => NameConflictBehavior::RENAME),
                'expected' => 'https://apis.live.net/v5.0/me/skydrive/files/test-file.txt?overwrite=ChooseNewName',
            ),

            'Parent given, RENAME name conflict behavior' => array(
                'name'     => 'test-file.txt',
                'parentId' => 'path/to/parent',
                'content'  => 'Some test content',
                'options'  => array('name_conflict_behavior' => NameConflictBehavior::RENAME),
                'expected' => 'https://apis.live.net/v5.0/path/to/parent/files/test-file.txt?overwrite=ChooseNewName',
            ),

            'Parent omitted, REPLACE name conflict behavior' => array(
                'name'     => 'test-file.txt',
                'parentId' => null,
                'content'  => 'Some test content',
                'options'  => array('name_conflict_behavior' => NameConflictBehavior::REPLACE),
                'expected' => 'https://apis.live.net/v5.0/me/skydrive/files/test-file.txt?overwrite=true',
            ),

            'Parent given, REPLACE name conflict behavior' => array(
                'name'     => 'test-file.txt',
                'parentId' => 'path/to/parent',
                'content'  => 'Some test content',
                'options'  => array('name_conflict_behavior' => NameConflictBehavior::REPLACE),
                'expected' => 'https://apis.live.net/v5.0/path/to/parent/files/test-file.txt?overwrite=true',
            ),
        );
    }

    /**
     * @dataProvider provideCreateFileShouldCallOnceCurlSetoptArrayWithExpectedUrl
     */
    public function testCreateFileShouldCallOnceCurlSetoptArrayWithExpectedUrl(
        $name,
        $parentId,
        $content,
        $options,
        $expected
    ) {
        GlobalNamespace::reset(array(
            'curl_setopt_array' => array(
                function ($expectation) {
                    $expectation
                        ->once()
                        ->andReturn(true);
                },
                function ($expectation) use ($expected) {
                    $expectation
                        ->once()
                        ->withArgs(function ($ch, $options) use ($expected) {
                            return array_key_exists(CURLOPT_URL, $options) && $options[CURLOPT_URL] == $expected;
                        });
                },
            ),

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'id' => 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                )));
            },
        ));

        $this
            ->client
            ->createFile($name, $parentId, $content, $options);
    }

    public function provideCreateFileShouldCallOnceFopenWithExpectedArguments()
    {
        return array(
            'MEMORY back end' => array(
                'options'  => array('stream_back_end' => StreamBackEnd::MEMORY),
                'expected' => array(
                    'filename' => 'php://memory',
                    'mode'     => 'rw+b',
                ),
            ),

            'TEMP back end' => array(
                'options'  => array('stream_back_end' => StreamBackEnd::TEMP),
                'expected' => array(
                    'filename' => 'php://temp',
                    'mode'     => 'rw+b',
                ),
            ),
        );
    }

    /**
     * @dataProvider provideCreateFileShouldCallOnceFopenWithExpectedArguments
     */
    public function testCreateFileShouldCallOnceFopenWithExpectedArguments(
        $options,
        $expected
    ) {
        GlobalNamespace::reset(array(
            'fopen' => function ($expectation) use ($expected) {
                $expectation
                    ->once()
                    ->withArgs(function ($filename, $mode) use ($expected) {
                        return $expected['filename'] == $filename && $expected['mode'] == $mode;
                    });
            },

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'id' => 'file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                )));
            },
        ));

        $client = $this->getClient($options);

        $client->createFile(
            'test-file.txt',
            'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
            'Some test content',
            $options
        );
    }

    public function provideFetchObjectShouldReturnExpectedType()
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
     * @dataProvider provideFetchObjectShouldReturnExpectedType
     */
    public function testFetchObjectShouldReturnExpectedType($type, $expected)
    {
        GlobalNamespace::reset(array(
            'curl_exec' => function ($expectation) use ($type) {
                $expectation->andReturn(json_encode((object) array(
                    'type' => $type,
                )));
            },
        ));

        $object = $this
            ->client
            ->fetchObject('some-resource');

        $actual = get_class($object);
        $this->assertEquals("Krizalys\Onedrive\\$expected", $actual);
    }

    public function testFetchRootShouldCallOnceCurlSetoptWithExpectedUrl()
    {
        GlobalNamespace::reset(array(
            'curl_setopt' => function ($expectation) {
                $expectation
                    ->once()
                    ->withArgs(function ($ch, $opt, $value) {
                        return CURLOPT_URL == $opt && 'https://apis.live.net/v5.0/me/skydrive?access_token=OlD%2FAcCeSs%2BToKeN' == $value;
                    });
            },

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'id'   => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                    'type' => 'folder',
                )));
            },
        ));

        $this
            ->client
            ->fetchRoot();
    }

    public function testFetchCameraRollShouldCallOnceCurlSetoptWithExpectedUrl()
    {
        GlobalNamespace::reset(array(
            'curl_setopt' => function ($expectation) {
                $expectation
                    ->once()
                    ->withArgs(function ($ch, $opt, $value) {
                        return CURLOPT_URL == $opt && 'https://apis.live.net/v5.0/me/skydrive/camera_roll?access_token=OlD%2FAcCeSs%2BToKeN' == $value;
                    });
            },

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'id'   => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                    'type' => 'folder',
                )));
            },
        ));

        $this
            ->client
            ->fetchCameraRoll();
    }

    public function testFetchDocsShouldCallOnceCurlSetoptWithExpectedUrl()
    {
        GlobalNamespace::reset(array(
            'curl_setopt' => function ($expectation) {
                $expectation
                    ->once()
                    ->withArgs(function ($ch, $opt, $value) {
                        return CURLOPT_URL == $opt && 'https://apis.live.net/v5.0/me/skydrive/my_documents?access_token=OlD%2FAcCeSs%2BToKeN' == $value;
                    });
            },

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'id'   => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                    'type' => 'folder',
                )));
            },
        ));

        $this
            ->client
            ->fetchDocs();
    }

    public function testFetchCameraPicsShouldCallOnceCurlSetoptWithExpectedUrl()
    {
        GlobalNamespace::reset(array(
            'curl_setopt' => function ($expectation) {
                $expectation
                    ->once()
                    ->withArgs(function ($ch, $opt, $value) {
                        return CURLOPT_URL == $opt && 'https://apis.live.net/v5.0/me/skydrive/my_photos?access_token=OlD%2FAcCeSs%2BToKeN' == $value;
                    });
            },

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'id'   => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                    'type' => 'folder',
                )));
            },
        ));

        $this
            ->client
            ->fetchPics();
    }

    public function testFetchPublicDocsShouldCallOnceCurlSetoptWithExpectedUrl()
    {
        GlobalNamespace::reset(array(
            'curl_setopt' => function ($expectation) {
                $expectation
                    ->once()
                    ->withArgs(function ($ch, $opt, $value) {
                        return CURLOPT_URL == $opt && 'https://apis.live.net/v5.0/me/skydrive/public_documents?access_token=OlD%2FAcCeSs%2BToKeN' == $value;
                    });
            },

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'id'   => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                    'type' => 'folder',
                )));
            },
        ));

        $this
            ->client
            ->fetchPublicDocs();
    }

    public function provideFetchPropertiesShouldCallOnceCurlSetoptWithExpectedUrl()
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
     * @dataProvider provideFetchPropertiesShouldCallOnceCurlSetoptWithExpectedUrl
     */
    public function testFetchPropertiesShouldCallOnceCurlSetoptWithExpectedUrl(
        $objectId,
        $expected
    ) {
        GlobalNamespace::reset(array(
            'curl_setopt' => function ($expectation) use ($expected) {
                $expectation
                    ->once()
                    ->withArgs(function ($ch, $opt, $value) use ($expected) {
                        return CURLOPT_URL == $opt && $expected == $value;
                    });
            },

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array()));
            },
        ));

        $this
            ->client
            ->fetchProperties($objectId);
    }

    public function provideFetchObjectsShouldCallOnceCurlSetoptWithExpectedUrl()
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
     * @dataProvider provideFetchObjectsShouldCallOnceCurlSetoptWithExpectedUrl
     */
    public function testFetchObjectsShouldCallOnceCurlSetoptWithExpectedUrl(
        $objectId,
        $expected
    ) {
        GlobalNamespace::reset(array(
            'curl_setopt' => function ($expectation) use ($expected) {
                $expectation
                    ->once()
                    ->withArgs(function ($ch, $opt, $value) use ($expected) {
                        return CURLOPT_URL == $opt && $expected == $value;
                    });
            },

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'data' => array(),
                )));
            },
        ));

        $this
            ->client
            ->fetchObjects($objectId);
    }

    public function testUpdateObjectShouldCallOnceCurlSetoptArrayWithExpectedUrl()
    {
        GlobalNamespace::reset(array(
            'curl_setopt_array' => array(
                function ($expectation) {
                    $expectation
                        ->once()
                        ->andReturn(true);
                },
                function ($expectation) {
                    $expectation
                        ->once()
                        ->withArgs(function ($ch, $options) {
                            return array_key_exists(CURLOPT_URL, $options) && 'https://apis.live.net/v5.0/file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123' == $options[CURLOPT_URL];
                        });
                },
            ),

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array()));
            },
        ));

        $this
            ->client
            ->updateObject('file.ffffffffffffffff.FFFFFFFFFFFFFFFF!123');
    }

    public function provideMoveObjectShouldCallOnceCurlSetoptArrayWithExpectedDestinationUrl()
    {
        return array(
            'Null destination ID' => array(
                'destinationId' => null,
                'expected'      => 'me/skydrive',
            ),

            'Non-null destination ID' => array(
                'destinationId' => 'path/to/object',
                'expected'      => 'path/to/object',
            ),
        );
    }

    /**
     * @dataProvider provideMoveObjectShouldCallOnceCurlSetoptArrayWithExpectedDestinationUrl
     */
    public function testMoveObjectShouldCallOnceCurlSetoptArrayWithExpectedDestinationUrl(
        $destinationId,
        $expected
    ) {
        GlobalNamespace::reset(array(
            'curl_setopt_array' => array(
                function ($expectation) {
                    $expectation
                        ->once()
                        ->andReturn(true);
                },
                function ($expectation) use ($expected) {
                    $expectation
                        ->once()
                        ->withArgs(function ($ch, $options) use ($expected) {
                            return array_key_exists(CURLOPT_POSTFIELDS, $options) && $expected == json_decode($options[CURLOPT_POSTFIELDS])->destination;
                        });
                },
            ),

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array()));
            },
        ));

        $this
            ->client
            ->moveObject('file.ffffffffffffffff.FFFFFFFFFFFFFFFF!456', $destinationId);
    }

    public function provideCopyFileShouldCallOnceCurlSetoptArrayWithExpectedDestinationUrl()
    {
        return array(
            'Null destination ID' => array(
                'destinationId' => null,
                'expected'      => 'me/skydrive',
            ),

            'Non-null destination ID' => array(
                'destinationId' => 'path/to/object',
                'expected'      => 'path/to/object',
            ),
        );
    }

    /**
     * @dataProvider provideCopyFileShouldCallOnceCurlSetoptArrayWithExpectedDestinationUrl
     */
    public function testCopyFileShouldCallOnceCurlSetoptArrayWithExpectedDestinationUrl(
        $destinationId,
        $expected
    ) {
        GlobalNamespace::reset(array(
            'curl_setopt_array' => array(
                function ($expectation) {
                    $expectation
                        ->once()
                        ->andReturn(true);
                },
                function ($expectation) use ($expected) {
                    $expectation
                        ->once()
                        ->withArgs(function ($ch, $options) use ($expected) {
                            return array_key_exists(CURLOPT_POSTFIELDS, $options) && $expected == json_decode($options[CURLOPT_POSTFIELDS])->destination;
                        });
                },
            ),

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array()));
            },
        ));

        $this
            ->client
            ->copyFile('file.ffffffffffffffff.FFFFFFFFFFFFFFFF!456', $destinationId);
    }

    public function testDeleteObjectShouldCallOnceCurlSetoptArrayWithExpectedUrl()
    {
        GlobalNamespace::reset(array(
            'curl_setopt_array' => array(
                function ($expectation) {
                    $expectation
                        ->once()
                        ->andReturn(true);
                },
                function ($expectation) {
                    $expectation
                        ->once()
                        ->withArgs(function ($ch, $options) {
                            return array_key_exists(CURLOPT_URL, $options) && 'https://apis.live.net/v5.0/file.ffffffffffffffff.FFFFFFFFFFFFFFFF!456?access_token=OlD%2FAcCeSs%2BToKeN' == $options[CURLOPT_URL];
                        });
                },
            ),

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array()));
            },
        ));

        $this
            ->client
            ->deleteObject('file.ffffffffffffffff.FFFFFFFFFFFFFFFF!456');
    }

    public function testFetchQuotaShouldCallOnceCurlSetoptWithExpectedUrl()
    {
        GlobalNamespace::reset(array(
            'curl_setopt' => function ($expectation) {
                $expectation
                    ->once()
                    ->withArgs(function ($ch, $opt, $value) {
                        return CURLOPT_URL == $opt && 'https://apis.live.net/v5.0/me/skydrive/quota?access_token=OlD%2FAcCeSs%2BToKeN' == $value;
                    });
            },

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'id'   => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                    'type' => 'folder',
                )));
            },
        ));

        $this
            ->client
            ->fetchQuota();
    }

    public function testFetchAccountInfoShouldCallOnceCurlSetoptWithExpectedUrl()
    {
        GlobalNamespace::reset(array(
            'curl_setopt' => function ($expectation) {
                $expectation
                    ->once()
                    ->withArgs(function ($ch, $opt, $value) {
                        return CURLOPT_URL == $opt && 'https://apis.live.net/v5.0/me?access_token=OlD%2FAcCeSs%2BToKeN' == $value;
                    });
            },

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'id'   => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                    'type' => 'folder',
                )));
            },
        ));

        $this
            ->client
            ->fetchAccountInfo();
    }

    public function testFetchRecentDocsShouldCallOnceCurlSetoptWithExpectedUrl()
    {
        GlobalNamespace::reset(array(
            'curl_setopt' => function ($expectation) {
                $expectation
                    ->once()
                    ->withArgs(function ($ch, $opt, $value) {
                        return CURLOPT_URL == $opt && 'https://apis.live.net/v5.0/me/skydrive/recent_docs?access_token=OlD%2FAcCeSs%2BToKeN' == $value;
                    });
            },

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'id'   => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                    'type' => 'folder',
                )));
            },
        ));

        $this
            ->client
            ->fetchRecentDocs();
    }

    public function testFetchSharedShouldCallOnceCurlSetoptWithExpectedUrl()
    {
        GlobalNamespace::reset(array(
            'curl_setopt' => function ($expectation) {
                $expectation
                    ->once()
                    ->withArgs(function ($ch, $opt, $value) {
                        return CURLOPT_URL == $opt && 'https://apis.live.net/v5.0/me/skydrive/shared?access_token=OlD%2FAcCeSs%2BToKeN' == $value;
                    });
            },

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(json_encode((object) array(
                    'id'   => 'folder.ffffffffffffffff.FFFFFFFFFFFFFFFF!123',
                    'type' => 'folder',
                )));
            },
        ));

        $this
            ->client
            ->fetchShared();
    }

    public function testLogShouldCallOnceLoggerLogWithExpectedArguments()
    {
        $logger = m::mock('Psr\Log\LoggerInterface');

        $logger
            ->shouldReceive('log')
            ->once()
            ->with(123, 'Test record', array('key' => 'value'));

        $client = new Client(array('logger' => $logger));
        $client->log(123, 'Test record', array('key' => 'value'));
    }
}
