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

if (!empty($_GET['id'])) {
	$properties['id'] = $_GET['id'];
}

if (!empty($_GET['destination_id'])) {
	$properties['destination_id'] = $_GET['destination_id'];
}

$id   = $_GET['id'];
$file = $onedrive->fetchObject($id);

if ($file->isFolder()) {
	throw new Exception('OneDrive does not support copying folders');
}

$file->copy($_GET['destination_id']);
?>
<!DOCTYPE html>
<html lang=en dir=ltr>
	<head>
		<meta charset=utf-8>
		<title>Copying a OneDrive file – Demonstration of the OneDrive SDK for PHP</title>
		<link rel=stylesheet href=//ajax.aspnetcdn.com/ajax/bootstrap/3.2.0/css/bootstrap.min.css>
		<link rel=stylesheet href=//ajax.aspnetcdn.com/ajax/bootstrap/3.2.0/css/bootstrap-theme.min.css>
		<meta name=viewport content="width=device-width, initial-scale=1">
	</head>
	<body>
		<div class=container>
			<h1>Copying a OneDrive file</h1>
			<p>The file <em><?php echo htmlspecialchars($id) ?></em> has been copied using the <code>File::copy</code> method.</p>
			<p class=bg-success>The file has been copied in your OneDrive account.</p>
			<p><a href=app.php>Back to the examples</a></p>
		</div>
	</body>
</html>
