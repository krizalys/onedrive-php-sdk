<?php
require_once __DIR__ . '/../onedrive-config.php';
require_once __DIR__ . '/../onedrive.php';

session_start();

if (!array_key_exists('onedrive.client.state', $_SESSION)) {
	throw new Exception('onedrive.client.state undefined in session');
}

$onedrive = new \Onedrive\Client(array(
	'state' => $_SESSION['onedrive.client.state']
));

$publicDocs = $onedrive->fetchPublicDocs();
?>
<!DOCTYPE html>
<html lang=en dir=ltr>
	<head>
		<meta charset=utf-8>
		<title>Fetching the OneDrive public documents – Demonstration of the OneDrive SDK for PHP</title>
		<link rel=stylesheet href=//ajax.aspnetcdn.com/ajax/bootstrap/3.2.0/css/bootstrap.min.css>
		<link rel=stylesheet href=//ajax.aspnetcdn.com/ajax/bootstrap/3.2.0/css/bootstrap-theme.min.css>
		<meta name=viewport content="width=device-width, initial-scale=1">
	</head>
	<body>
		<div class=container>
			<h1>Fetching the OneDrive public documents</h1>
			<p>The <code>Client::fetchPublicDocs</code> method returned:</p>
			<pre><?php print_r($publicDocs) ?></pre>
			<p><a href=app.php>Back to the examples</a></p>
		</div>
	</body>
</html>
