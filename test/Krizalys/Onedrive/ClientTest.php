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

        public function testObtainAccessToken()
        {
            self::$functions
                ->shouldReceive('time')
                ->andReturn(strtotime('1999-01-01Z'));

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

    function curl_init()
    {
        return null;
    }

    function curl_setopt_array()
    {
    }

    function curl_exec()
    {
        $data = ClientTest::mockTokenData('NeW');
        return json_encode($data);
    }
}
