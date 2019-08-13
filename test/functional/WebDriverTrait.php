<?php

namespace Test\Functional\Krizalys\Onedrive;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

trait WebDriverTrait
{
    private static $arguments = [
        '--headless',
        '--incognito',
    ];

    private static function withWebDriver($webDriverBaseUri, callable $callback)
    {
        $opts = new ChromeOptions();
        $opts->addArguments(self::$arguments);
        $caps = DesiredCapabilities::chrome();
        $caps->setCapability(ChromeOptions::CAPABILITY, $opts);
        $webDriver = RemoteWebDriver::create($webDriverBaseUri, $caps);

        try {
            return $callback($webDriver);
        } catch (\Exception $exception) {
            throw $exception;
        } finally {
            $webDriver->quit();
        }
    }
}
