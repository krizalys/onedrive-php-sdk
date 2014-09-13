<?php
require_once __DIR__ . '/../onedrive-config.php';
require_once __DIR__ . '/../onedrive.php';

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
?>
<!DOCTYPE html>
<html lang=en dir=ltr>
	<head>
		<meta charset=utf-8>
		<title>Demonstration of the OneDrive SDK for PHP</title>
		<link rel=stylesheet href=//ajax.aspnetcdn.com/ajax/bootstrap/3.2.0/css/bootstrap.min.css>
		<link rel=stylesheet href=//ajax.aspnetcdn.com/ajax/bootstrap/3.2.0/css/bootstrap-theme.min.css>
		<meta name=viewport content="width=device-width, initial-scale=1">
		<meta name=description content="This website provides a live demonstration of the OneDrive SDK for PHP, by Krizalys. See krizalys.com for more details.">
	</head>
	<body>
		<div class=container>
			<h1>Demonstration of the OneDrive SDK for PHP</h1>
			<div class=jumbotron>
				<p><a href="<?php echo htmlspecialchars($url) ?>" class="btn btn-primary btn-lg">Log in to OneDrive</a></p>
			</div>
		</div>
	</body>
</html>
