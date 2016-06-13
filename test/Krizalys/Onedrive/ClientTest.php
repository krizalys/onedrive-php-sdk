<?php

namespace Test\Krizalys\Onedrive
{
    use Krizalys\Onedrive\Client;

    class ClientTest extends \PHPUnit_Framework_TestCase
    {
        public static function mockTokenData($prefix = 'OlD')
        {
            return (object) [
                'token_type'           => 'bearer',
                'expires_in'           => 3600,
                'scope'                => 'wl.signin wl.basic wl.contacts_skydrive wl.skydrive_update wl.offline_access',
                'access_token'         => "$prefix/AcCeSs+ToKeN",
                'refresh_token'        => "$prefix!ReFrEsH*ToKeN",
                'authentication_token' => "$prefix.AuThEnTiCaTiOn_ToKeN",
                'user_id'              => 'ffffffffffffffffffffffffffffffff',
            ];
        }

        public function testRenewAccessToken()
        {
            $client = new Client(array(
                'client_id' => '9999999999999999',
                'state'     => (object) array(
                    'redirect_uri' => null,
                    'token'        => (object) array(
                        'obtained' => strtotime('1999-01-01Z'),
                        'data'     => self::mockTokenData(),
                    ),
                ),
            ));

            $client->renewAccessToken('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
            $actual = $client->getState();

            $this->assertEquals((object) array(
                'redirect_uri' => null,
                'token'        => (object) [
                    'obtained' => strtotime('1999-12-01Z'),
                    'data'     =>  (object) [
                        'token_type'           => 'bearer',
                        'expires_in'           => 3600,
                        'scope'                => 'wl.signin wl.basic wl.contacts_skydrive wl.skydrive_update wl.offline_access',
                        'access_token'         => "NeW/AcCeSs+ToKeN",
                        'refresh_token'        => "NeW!ReFrEsH*ToKeN",
                        'authentication_token' => "NeW.AuThEnTiCaTiOn_ToKeN",
                        'user_id'              => 'ffffffffffffffffffffffffffffffff',
                    ],
                ],
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
        return strtotime('1999-12-01Z');
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
