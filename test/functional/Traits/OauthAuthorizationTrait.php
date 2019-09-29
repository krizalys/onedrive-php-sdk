<?php

namespace Test\Functional\Krizalys\Onedrive\Traits;

use Facebook\WebDriver\WebDriver;
use Krizalys\Onedrive\Client;
use Symfony\Component\Process\Process;

trait OauthAuthorizationTrait
{
    use MicrosoftOauthAuthenticationTrait;
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

    private static function authorize(Client $client, $username, $password, $state = null)
    {
        // Random registered port.
        $redirectUriPort = rand(self::$minRedirectPort, self::$maxRedirectPort);

        $redirectUri             = sprintf(self::$redirectUriTemplate, $redirectUriPort);
        $authorizationRequestUri = $client->getLogInUrl(self::$scopes, $redirectUri, $state);
        $webDriverBaseUri        = sprintf(self::$webDriverBaseUriTemplate, self::$webDriverBaseUriPort);
        $root                    = dirname(__DIR__);

        $command = [
            'php',
            '-S',
            sprintf('localhost:%d', $redirectUriPort),
            sprintf('%s/Router.php', $root),
        ];

        return self::withProcess($command, function (Process $process) use ($webDriverBaseUri, $authorizationRequestUri, $redirectUri, $username, $password) {
            self::withWebDriver($webDriverBaseUri, function (WebDriver $webDriver) use ($authorizationRequestUri, $redirectUri, $username, $password) {
                self::authenticate($webDriver, $authorizationRequestUri, $redirectUri, $username, $password);
            });

            foreach ($process as $type => $buffer) {
                if ($type == Process::OUT) {
                    $lines  = explode("\n", $buffer);
                    $values = self::extractRedirectUriQueryStringValues($lines);

                    if ($values !== null) {
                        return $values;
                    }
                } else {
                    throw new \Exception("Unsupported process output type: $type");
                }
            }

            return null;
        });
    }

    private static function extractRedirectUriQueryStringValues(array $lines)
    {
        foreach ($lines as $line) {
            $line = json_decode($line, true);

            if ($line !== null || json_last_error() == JSON_ERROR_NONE) {
                 return $line;
            }
        }

        return null;
    }
}
