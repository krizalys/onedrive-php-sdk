<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Krizalys\Onedrive\Client;

session_start();

try {
    if (!array_key_exists('onedrive.client.state', $_SESSION)) {
        throw new \Exception('onedrive.client.state undefined in session');
    }

    $onedrive = new Client(array(
        'state' => $_SESSION['onedrive.client.state'],
    ));

    if (!array_key_exists('id', $_GET)) {
        throw new \Exception('id undefined in $_GET');
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
    $status = sprintf('<p class=bg-success>The object <em>%s</em> has been updated in your OneDrive account using the <code>Client::updateObject</code> method.</p>', htmlspecialchars($id));
} catch (\Exception $e) {
    $status = sprintf('<p class=bg-danger>The object <em>%s</em> has <strong>not</strong> been updated in your OneDrive account using the <code>Client::updateObject</code> method. Reason: <cite>%s</cite></p>', htmlspecialchars($id), htmlspecialchars($e->getMessage()));
}
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
            <?php if (null !== $status) echo $status ?>
            <p><a href=app.php>Back to the examples</a></p>
        </div>
    </body>
</html>
