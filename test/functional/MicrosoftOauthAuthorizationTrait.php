<?php

namespace Test\Functional\Krizalys\Onedrive;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

trait MicrosoftOauthAuthorizationTrait
{
    private static $usernameElementId = 'i0116';

    private static $passwordElementId = 'i0118';

    private static $nextElementId = 'idSIButton9';

    private static function requestAuthorization(
        WebDriver $webDriver,
        $authorizationRequestUri,
        $redirectUri,
        $username,
        $password
    ) {
        $webDriver->get($authorizationRequestUri);

        $nextElementLocator = WebDriverBy::id(self::$nextElementId);

        $usernameElementLocator = WebDriverBy::id(self::$usernameElementId);
        self::findElement($webDriver, $usernameElementLocator)->sendKeys($username);

        self::findElement($webDriver, $nextElementLocator)->click();

        $passwordElementLocator = WebDriverBy::id(self::$passwordElementId);
        self::findElement($webDriver, $passwordElementLocator)->sendKeys($password);

        self::findElement($webDriver, $nextElementLocator)->click();

        $expectedUri = preg_quote($redirectUri);
        $isMatching  = WebDriverExpectedCondition::urlMatches("|^$expectedUri|");
        $webDriver->wait()->until($isMatching);
    }

    private static function findElement(WebDriver $webDriver, WebDriverBy $locator)
    {
        $isPresent = WebDriverExpectedCondition::presenceOfElementLocated($locator);
        $webDriver->wait()->until($isPresent);

        $isVisible = WebDriverExpectedCondition::visibilityOfElementLocated($locator);
        $webDriver->wait()->until($isVisible);

        return $webDriver->findElement($locator);
    }
}
