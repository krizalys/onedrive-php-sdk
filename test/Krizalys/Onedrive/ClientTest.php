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
            self::$functions
                ->shouldReceive('time')
                ->andReturn(strtotime('1999-01-01Z'));

            self::$functions
                ->shouldReceive('curl_exec')
                ->andReturn(json_encode(ClientTest::mockTokenData('NeW')));

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
            self::$functions
                ->shouldReceive('time')
                ->andReturn(strtotime('1999-12-31Z'));

            self::$functions
                ->shouldReceive('curl_exec')
                ->andReturn(json_encode(ClientTest::mockTokenData('NeW')));

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
                    'data'     =>  (object) array(
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
            self::$functions
                ->shouldReceive('curl_exec')
                ->andReturn(json_encode(array(
                    'key' => 'value',
                )));

            self::$functions
                ->shouldReceive('curl_getinfo')
                ->andReturn(array(
                    'content_type' => 'application/json',
                ));

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
            self::$functions
                ->shouldReceive('curl_exec')
                ->andReturn(json_encode(array(
                    'output_key' => 'output_value',
                )));

            self::$functions
                ->shouldReceive('curl_getinfo')
                ->andReturn(array(
                    'content_type' => 'application/json',
                ));

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
            self::$functions
                ->shouldReceive('curl_exec')
                ->andReturn(json_encode(array(
                    'key' => 'value',
                )));

            self::$functions
                ->shouldReceive('curl_getinfo')
                ->andReturn(array(
                    'content_type' => 'application/json',
                ));

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
            self::$functions
                ->shouldReceive('curl_exec')
                ->andReturn(json_encode(array(
                    'key' => 'value',
                )));

            self::$functions
                ->shouldReceive('curl_getinfo')
                ->andReturn(array(
                    'content_type' => 'application/json',
                ));

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
            self::$functions
                ->shouldReceive('curl_exec')
                ->andReturn(json_encode(array(
                    'output_key' => 'output_value',
                )));

            self::$functions
                ->shouldReceive('curl_getinfo')
                ->andReturn(array(
                    'content_type' => 'application/json',
                ));

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
            self::$functions
                ->shouldReceive('curl_exec')
                ->andReturn(json_encode(array(
                    'output_key' => 'output_value',
                )));

            self::$functions
                ->shouldReceive('curl_getinfo')
                ->andReturn(array(
                    'content_type' => 'application/json',
                ));

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
        return null;
    }

    function curl_setopt()
    {
    }

    function curl_setopt_array()
    {
    }

    function curl_exec($curl)
    {
        return ClientTest::$functions->curl_exec($curl);
    }

    function curl_getinfo($curl, $option = 0)
    {
        return ClientTest::$functions->curl_getinfo($curl, $option);
    }
}
