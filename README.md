OneDrive SDK for PHP
====================

[![Latest Stable Version](https://poser.pugx.org/krizalys/onedrive-php-sdk/v/stable)](https://packagist.org/packages/krizalys/onedrive-php-sdk)
[![Total Downloads](https://poser.pugx.org/krizalys/onedrive-php-sdk/d/total.svg)](https://packagist.org/packages/krizalys/onedrive-php-sdk)
[![Build Status](https://travis-ci.org/krizalys/onedrive-php-sdk.svg?branch=master)](https://travis-ci.org/krizalys/onedrive-php-sdk)
[![Code Coverage](https://codecov.io/gh/krizalys/onedrive-php-sdk/branch/master/graph/badge.svg)](https://codecov.io/gh/krizalys/onedrive-php-sdk)
[![StyleCI](https://styleci.io/repos/23994489/shield?style=flat)](https://styleci.io/repos/23994489)

OneDrive SDK for PHP is an open source library that allows [PHP][php]
applications to interact programmatically with the [OneDrive REST
API][onedrive-rest-api].

It supports operations such as creating, reading, updating, deleting (CRUD)
files and folders, as well as moving or copying them to other folders.

Requirements
------------

Using the OneDrive SDK for PHP requires the following:

* [PHP][php] 5.6 or newer
* [Composer][composer] 1.0.0-alpha10 or newer
* Basic PHP knowledge

### Testing

For development, you also require:

* A OneDrive web application configured with `http://localhost:7777/` as its
  redirect URL
* A WebDriver server, for example the [Selenium's Java standalone
  server][selenium-server-standalone]
* A Chrome browser & ChromeDriver, and they must be usable by the WebDriver
  server

Installation
------------

From the root of this repository, get the required dependencies using
[Composer][composer]:

```
$ composer install -n --no-dev
```

During the process, a `vendor/autoload.php` file will be created and allows you
to use classes from OneDrive SDK for PHP without needing to explicitly
`require()` files that define them.

Quick start
-----------

To use this SDK, you need to register a OneDrive application. To do this, first
[sign in to your Microsoft account][microsoft-account-login], then visit the
[Application Registration Portal][app-registration-portal] and [create an
application][register-app].

Once done, your application will be assigned, among other things, an
*Application ID*, referred to as the *Client ID*. You will be able to add
*Application Secrets*, some of them of type *Password*, and whose values are
referred to as *Client Secrets*. The *Client ID* and a *Client Secret* will be
needed shortly to configure the OneDrive SDK for PHP.

You also need to create a web page where users will get redirected after they
successfully signed in to their OneDrive account using this SDK. Typically, this
page will be a PHP script where you will start to interact with the files and
folders stored in their OneDrive account. The URL of this page should be listed
as a *Redirect URL*, referred to as a *Redirect URI*, and will also be needed to
configure the OneDrive SDK for PHP.

Once you got your *Client ID*, a *Client Secret* and a *Redirect URI*, you can
get started using the OneDrive SDK for PHP in three steps.

### Step 1: create your configuration

As you may need them from several scripts, save your *Client ID*, *Client
Secret* and *Redirect URI* in a configuration file, returning values like this:

```php
<?php
// config.php

return [
    /**
     * Your OneDrive client ID.
     */
    'ONEDRIVE_CLIENT_ID' => '<YOUR_CLIENT_ID>',

    /**
     * Your OneDrive client secret.
     */
    'ONEDRIVE_CLIENT_SECRET' => '<YOUR_CLIENT_SECRET>',

    /**
     * Your OneDrive redirect URI.
     */
    'ONEDRIVE_REDIRECT_URI' => 'http://your.domain.com/redirect.php',
];
?>
```

### Step 2: direct your users to the sign in page

This script is responsible for, given a set of privileges, fetching a login URL
from the OneDrive API. It then needs to guide the users to this URL so they
initiate their log in and privilege granting process. The script should look
like (replace `/path/to` by the appropriate values):

```php
<?php
// index.php

($config = include '/path/to/config.php') or die('Configuration file not found');
require_once '/path/to/onedrive-php-sdk/vendor/autoload.php';

use GuzzleHttp\Client as GuzzleHttpClient;
use Krizalys\Onedrive\Client;
use Microsoft\Graph\Graph;
use Monolog\Logger;

// Instantiates a OneDrive client bound to your OneDrive application.
$client = new Client(
    $config['ONEDRIVE_CLIENT_ID'],
    new Graph(),
    new GuzzleHttpClient(
        ['base_uri' => 'https://graph.microsoft.com/v1.0/']
    ),
    new Logger('Krizalys\Onedrive\Client')
);

// Gets a log in URL with sufficient privileges from the OneDrive API.
$url = $client->getLogInUrl([
    'files.read',
    'files.read.all',
    'files.readwrite',
    'files.readwrite.all',
    'offline_access',
], $config['ONEDRIVE_REDIRECT_URI']);

session_start();

// Persist the OneDrive client' state for next API requests.
$_SESSION = [
    'onedrive.client.state' => $client->getState(),
];

// Guide the user to the log in URL (you may also use an HTTP/JS redirect).
echo "<a href='$url'>Next step</a>";
?>
```

### Step 3: get an OAuth access token

After the users follow this URL, they are required to sign in using a valid
Microsoft account, and they are asked whether they agree to allow your
application to access their OneDrive account.

If they do, they are redirected back to your *Redirect URI* and a code is passed
in the query string of this URL. The script residing at this URL essentially:

1. Instantiates a `Client` from your configuration and the state from previous
instantiations
2. Obtains an OAuth access token using `Client::obtainAccessToken()`
passing it the code received
3. May start interacting with the files and folders stored in their OneDrive
account, or delegates this responsibility to other scripts instantiating a
`Client` from the same state

It typically looks like (replace `/path/to` by the appropriate values):

```php
<?php
// redirect.php

($config = include '/path/to/config.php') or die('Configuration file not found');
require_once '/path/to/onedrive-php-sdk/vendor/autoload.php';

use GuzzleHttp\Client as GuzzleHttpClient;
use Krizalys\Onedrive\Client;
use Microsoft\Graph\Graph;
use Monolog\Logger;

// If we don't have a code in the query string (meaning that the user did not
// log in successfully or did not grant privileges requested), we cannot proceed
// in obtaining an access token.
if (!array_key_exists('code', $_GET)) {
    throw new \Exception('code undefined in $_GET');
}

session_start();

// Attempt to load the OneDrive client' state persisted from the previous
// request.
if (!array_key_exists('onedrive.client.state', $_SESSION)) {
    throw new \Exception('onedrive.client.state undefined in $_SESSION');
}

$client = new Client(
    $config['ONEDRIVE_CLIENT_ID'],
    new Graph(),
    new GuzzleHttpClient(
        ['base_uri' => 'https://graph.microsoft.com/v1.0/']
    ),
    new Logger('Krizalys\Onedrive\Client'),
    [
        // Restore the previous state while instantiating this client to proceed
        // in obtaining an access token.
        'state' => $_SESSION['onedrive.client.state']
    ]
);

// Obtain the token using the code received by the OneDrive API.
$client->obtainAccessToken($config['ONEDRIVE_CLIENT_SECRET'], $_GET['code']);

// Persist the OneDrive client' state for next API requests.
$_SESSION['onedrive.client.state'] = $client->getState();

// Past this point, you can start using file/folder functions from the SDK, eg:
var_dump($client->getDrives());
?>
```

For details about classes and methods available, see the [project
page][ondrive-php-sdk] on [Krizalys][krizalys].

Testing
-------

To run the functional test suite:

1. Set your application configuration at `test/functional/config.php` ;
2. Run your WebDriver server, for example:

```
java -jar selenium-server-standalone-3.14.0.jar
```

3. Run the functional test (it assumes that your Selenium WebDriver is listening
   on port 4444):

```
vendor/bin/phpunit -c test/functional
```

4. Repeat steps 4 to 5 as needed.

License
-------

The OneDrive SDK for PHP is licensed under the [BSD 3-Clause
License][bsd-3-clause].

Credits
-------

The OneDrive SDK for PHP is developed and maintained by Christophe Vidal.

[php]:                        http://php.net/
[onedrive-rest-api]:          https://docs.microsoft.com/en-us/onedrive/developer/rest-api/?view=odsp-graph-online
[composer]:                   https://getcomposer.org/
[selenium-server-standalone]: http://selenium-release.storage.googleapis.com/index.html
[microsoft-account-login]:    https://login.live.com/
[app-registration-portal]:    https://apps.dev.microsoft.com/
[register-app]:               https://apps.dev.microsoft.com/portal/register-app
[ondrive-php-sdk]:            http://www.krizalys.com/software/onedrive-php-sdk
[krizalys]:                   http://www.krizalys.com/
[bsd-3-clause]:               https://opensource.org/licenses/BSD-3-Clause
