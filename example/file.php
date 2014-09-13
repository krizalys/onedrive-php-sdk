<?php
require_once __DIR__ . '/../onedrive-config.php';
require_once __DIR__ . '/../onedrive.php';

session_start();

if (!array_key_exists('id', $_GET)) {
	throw new Exception('id undefined in $_GET');
}

if (!array_key_exists('onedrive.client.state', $_SESSION)) {
	throw new Exception('onedrive.client.state undefined in session');
}

$onedrive = new \Onedrive\Client(array(
	'state' => $_SESSION['onedrive.client.state']
));

$id   = $_GET['id'];
$file = $onedrive->fetchObject($id);
?>
<!DOCTYPE html>
<html lang=en dir=ltr>
	<head>
		<meta charset=utf-8>
		<title>Fetching a OneDrive file – Demonstration of the OneDrive SDK for PHP</title>
		<link rel=stylesheet href=//ajax.aspnetcdn.com/ajax/bootstrap/3.2.0/css/bootstrap.min.css>
		<link rel=stylesheet href=//ajax.aspnetcdn.com/ajax/bootstrap/3.2.0/css/bootstrap-theme.min.css>
		<meta name=viewport content="width=device-width, initial-scale=1">
	</head>
	<body>
		<div class=container>
			<h1>Fetching a OneDrive file</h1>
			<p>The <code>Client::fetchObject</code> method returned the file <em><?php echo htmlspecialchars($id) ?></em>.</p>
			<h2>Properties</h2>
			<pre><?php print_r($file->fetchProperties()) ?></pre>
			<h2>Content</h2>
			<pre><?php echo htmlspecialchars($file->fetchContent()) ?></pre>
			<p><a href=app.php>Back to the examples</a></p>
		</div>
	</body>
</html>
