<?php

namespace Test\Functional\Krizalys\Onedrive;

use Facebook\WebDriver\WebDriver;
use GuzzleHttp\Client as GuzzleHttpClient;
use Krizalys\Onedrive\Client;
use Microsoft\Graph\Graph;
use Symfony\Component\Process\Process;

trait ClientFactoryTrait
{
    use MicrosoftOauthAuthorizationTrait;
    use ProcessTrait;
    use WebDriverTrait;

    private static $scopes = [
        'files.read',
        'files.read.all',
        'files.readwrite',
        'files.readwrite.all',
        'offline_access',
    ];

    private static $minRedirectPort = 1024;

    private static $maxRedirectPort = 49151;

    private static $redirectUriTemplate = 'http://localhost:%d/';

    private static $webDriverBaseUriTemplate = 'http://localhost:%d/wd/hub';

    private static $webDriverBaseUriPort = 4444;

    private static $uuidRegex = '/M[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/';

    private static function createClient($clientId, $username, $password, $secret)
    {
        $graph      = new Graph();
        $httpClient = new GuzzleHttpClient();
        $client     = new Client($clientId, $graph, $httpClient);

        // Random registered port.
        $redirectUriPort = rand(self::$minRedirectPort, self::$maxRedirectPort);

        $redirectUri             = sprintf(self::$redirectUriTemplate, $redirectUriPort);
        $authorizationRequestUri = $client->getLogInUrl(self::$scopes, $redirectUri);
        $webDriverBaseUri        = sprintf(self::$webDriverBaseUriTemplate, self::$webDriverBaseUriPort);

        $command = [
            'php',
            '-S',
            sprintf('localhost:%d', $redirectUriPort),
            sprintf('%s/router.php', __DIR__),
        ];

        $code = self::withProcess($command, function (Process $process) use ($webDriverBaseUri, $authorizationRequestUri, $redirectUri, $username, $password) {
            self::withWebDriver($webDriverBaseUri, function (WebDriver $webDriver) use ($authorizationRequestUri, $redirectUri, $username, $password) {
                self::requestAuthorization($webDriver, $authorizationRequestUri, $redirectUri, $username, $password);
            });

            foreach ($process as $type => $buffer) {
                if ($type == Process::OUT) {
                    $lines = explode("\n", $buffer);
                    $code  = self::findAuthorizationCode($lines);

                    if ($code !== null) {
                        break;
                    }
                } else {
                    throw new \Exception($buffer);
                }
            }

            return $code;
        });

        $client->obtainAccessToken($secret, $code);

        return $client;
    }

    private static function findAuthorizationCode(array $lines)
    {
        foreach ($lines as $line) {
            $line = trim($line);

            if (preg_match(self::$uuidRegex, $line)) {
                return $line;
            }
        }
    }
}
