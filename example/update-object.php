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

if (!array_key_exists('id', $_GET)) {
	throw new Exception('id undefined in $_GET');
}

$properties = array();

if (!empty($_GET['name'])) {
	$properties['name'] = $_GET['name'];
}

if (!empty($_GET['description'])) {
	$properties['description'] = $_GET['description'];
}

$id = $_GET['id'];
$onedrive->updateObject($id, $properties);
?>
<!DOCTYPE html>
<html lang=en dir=ltr>
	<head>
		<meta charset=utf-8>
		<title>Updating a OneDrive object – Demonstration of the OneDrive SDK for PHP</title>
		<link rel=stylesheet href=//ajax.aspnetcdn.com/ajax/bootstrap/3.2.0/css/bootstrap.min.css>
		<link rel=stylesheet href=//ajax.aspnetcdn.com/ajax/bootstrap/3.2.0/css/bootstrap-theme.min.css>
		<meta name=viewport content="width=device-width, initial-scale=1">
	</head>
	<body>
		<div class=container>
			<h1>Updating a OneDrive object</h1>
			<p class=bg-success>The object <em><?php echo htmlspecialchars($id) ?></em> has been updated in your OneDrive account using the <code>Client::updateObject</code> method.</p>
			<p><a href=app.php>Back to the examples</a></p>
		</div>
	</body>
</html>
