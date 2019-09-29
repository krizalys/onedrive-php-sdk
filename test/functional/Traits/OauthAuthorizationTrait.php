<?php

namespace Test\Functional\Krizalys\Onedrive\Traits;

use Facebook\WebDriver\WebDriver;
use Krizalys\Onedrive\Client;
use Symfony\Component\Process\Process;

trait OauthAuthorizationTrait
{
    use MicrosoftOauthAuthenticationTrait;
    use ProcessTrait;
    use WebdriverTrait;

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

    private static $webdriverBaseUriTemplate = 'http://localhost:%d/wd/hub';

    private static $webdriverBaseUriPort = 4444;

    private static function authorize(Client $client, $username, $password, $state = null)
    {
        // Random registered port.
        $redirectUriPort = rand(self::$minRedirectPort, self::$maxRedirectPort);

        $redirectUri             = sprintf(self::$redirectUriTemplate, $redirectUriPort);
        $authorizationRequestUri = $client->getLogInUrl(self::$scopes, $redirectUri, $state);
        $webdriverBaseUri        = sprintf(self::$webdriverBaseUriTemplate, self::$webdriverBaseUriPort);
        $root                    = dirname(__DIR__);

        $command = [
            'php',
            '--server',
            sprintf('localhost:%d', $redirectUriPort),
            sprintf('%s/Router.php', $root),
        ];

        return self::withProcess($command, function (Process $process) use ($webdriverBaseUri, $authorizationRequestUri, $redirectUri, $username, $password) {
            self::withWebdriver($webdriverBaseUri, function (WebDriver $webdriver) use ($authorizationRequestUri, $redirectUri, $username, $password) {
                self::authenticate($webdriver, $authorizationRequestUri, $redirectUri, $username, $password);
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
