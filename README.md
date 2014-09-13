OneDrive SDK for PHP
====================

OneDrive SDK for PHP is an open source library that allows
[PHP](http://php.net/) applications to interact programmatically with the
[OneDrive API](http://msdn.microsoft.com/en-us/library/hh826521.aspx).

It supports operations such as creating, reading, updating, deleting (CRUD)
files and folders, as well as moving or copying them to other folders.

Requirements
------------

Using the OneDrive SDK for PHP requires the following:

* [PHP](http://php.net/) 5.3 or newer
* The [cURL extension for PHP](http://php.net/manual/en/book.curl.php)
* Basic PHP knowledge

Installation
------------

To install the OneDrive SDK for PHP, copy the `onedrive-php-sdk` folder in your
application source tree. The `example` subfolder contains example files and may
be removed from production servers.

Configuration
-------------

To use this SDK, you need to register a OneDrive application. To do this, first
[sign in to your Microsoft account](https://login.live.com/), then visit your
[application manager](https://account.live.com/developers/applications/index)
and
[create an application](https://account.live.com/developers/applications/create).

Once done, your application will be assigned, among other things, a *Client ID*
and a *Client secret*. These two values will be needed shortly to configure the
OneDrive SDK for PHP.

You also need to create a web page where users will get redirected after they
successfully signed in to their OneDrive account using this SDK. Typically, this
will page will be a PHP script where you will start to interact with the files
and folders stored in their OneDrive account. The URL of this page is the
*Callback URI* and will also be needed to configure the OneDrive SDK for PHP.

Quick start
-----------

Once you got your *Client ID*, *Client secret* and *Callback URI*, you can get
started using the OneDrive SDK for PHP in three steps.

### Step 1: save your configuration

As you may need them from several scripts, save your *Client ID*, *Client
secret* and *Callback URI* in a configuration file. Let's call it
`onedrive-config.php` and fill it with:

```php
<?php
/*
 * Your OneDrive client ID.
 */
define('ONEDRIVE_CLIENT_ID',     '<YOUR_CLIENT_ID>'    );

/*
 * Your OneDrive client secret.
 */
define('ONEDRIVE_CLIENT_SECRET', '<YOUR_CLIENT_SECRET>');

/*
 * Your OneDrive callback URI.
 */
define('ONEDRIVE_CALLBACK_URI',  '<http://yourdomain.com/your-callback-uri.php>');
?>
```

### Step 2: direct your users to the sign in page

This script is responsible for, given a set of privileges, fetching a login URL
from the OneDrive API. It then needs to guide the users to this URL so they
initiate their log in and privilege granting process. The script should look
like (replace `/path/to` by the appropriate values):

```php
<?php
require_once '/path/to/onedrive-config.php';
require_once '/path/to/onedrive-php-sdk/onedrive.php';

session_start();

// Clears the session and restarts the whole authentication process
$_SESSION = array();

// Instantiates a OneDrive client bound to your OneDrive application
$onedrive = new \Onedrive\Client(array(
	'client_id' => ONEDRIVE_CLIENT_ID
));

// Gets a log in URL with sufficient privileges from the OneDrive API
$url = $onedrive->getLogInUrl(array(
	'wl.signin',
	'wl.basic',
	'wl.contacts_skydrive',
	'wl.skydrive_update'
), ONEDRIVE_CALLBACK_URI);

// Persist the OneDrive client' state for next API requests
$_SESSION['onedrive.client.state'] = $onedrive->getState();

// Guide the user to the log in URL (you may also use an HTTP/JS redirect)
echo "<a href='$url'>Next step</a>";
?>
```

### Step 3: get an OAuth access token

After the users follow this URL, they are required to sign in using a valid
Microsoft account, and they are asked whether they agree to allow your
application to access their OneDrive account.

If they do, they are redirected back to your *Callback URI* and a code is passed
in the query string of this URL. The script residing at this URL is responsible
for obtaining an access token (from the code received) and should start like
(replace `/path/to` by the appropriate values):

```php
<?php
require_once '/path/to/onedrive-config.php';
require_once '/path/to/onedrive-php-sdk/onedrive.php';

// If we don't have a code in the query string (meaning that the user did not
// log in successfully or did not grant privileges requested), we cannot proceed
// in obtaining an access token
if (!array_key_exists('code', $_GET)) {
	throw new Exception('code undefined in $_GET');
}

session_start();

// Attempt to load the OneDrive client' state persisted from the previous
// request
if (!array_key_exists('onedrive.client.state', $_SESSION)) {
	throw new Exception('onedrive.client.state undefined in $_SESSION');
}

$onedrive = new \Onedrive\Client(array(
	'client_id' => ONEDRIVE_CLIENT_ID,

	// Restore the previous state while instantiating this client to proceed in
	// obtaining an access token
	'state'     => $_SESSION['onedrive.client.state']
));

// Obtain the token using the code received by the OneDrive API
$onedrive->obtainAccessToken(ONEDRIVE_CLIENT_SECRET, $_GET['code']);

// Persist the OneDrive client' state for next API requests
$_SESSION['onedrive.client.state'] = $onedrive->getState();

// Past this point, you can start using file/folder functions from the SDK
?>
```

For details about classes and methods available, see the
[project page](http://www.krizalys.com/software/onedrive-php-sdk) on
[Krizalys](http://www.krizalys.com/).

Examples
--------

To demonstrate the use of the OneDrive SDK for PHP, examples are provided with
the library, in the `example` subfolder. Using the examples require a file
`config.php` to be present in the `onedrive-php-sdk` folder and filled as
explained in the step 1 from the Quick Start. A OneDrive account is needed to
try out these examples.

Demonstration
-------------

The example files provided with the OneDrive SDK for PHP are deployed on a
[demo website](http://demo.krizalys.com/onedrive-php-sdk/example/) for live
demonstration purposes. A OneDrive account is needed to try out this
demonstration.

License
-------

The OneDrive SDK for PHP is licensed under the
[GNU General Public License v3.0](http://www.gnu.org/copyleft/gpl.html).

Credits
-------

The OneDrive SDK for PHP is developed and maintained by Christophe Vidal.
