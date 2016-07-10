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

    if (!array_key_exists('name', $_GET)) {
        throw new \Exception('name undefined in $_GET');
    }

    $parentId    = empty($_GET['parent_id']) ? null : $_GET['parent_id'];
    $name        = $_GET['name'];
    $description = empty($_GET['description']) ? null : $_GET['description'];
    $parent      = $onedrive->fetchObject($parentId);
    $folder      = $parent->createFolder($name, $description);
    $status      = sprintf('<p class=bg-success>The folder <em>%s</em> has been created using the <code>Object::createFolder</code> method.</p>', htmlspecialchars($name));
} catch (\Exception $e) {
    $folder = null;
    $status = sprintf('<p class=bg-danger>The folder <em>%s</em> has <strong>not</strong> been created using the <code>Object::createFolder</code> method. Reason: <cite>%s</cite></p>', htmlspecialchars($name), htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang=en dir=ltr>
    <head>
        <meta charset=utf-8>
        <title>Creating a OneDrive folder – Demonstration of the OneDrive SDK for PHP</title>
        <link rel=stylesheet href=//ajax.aspnetcdn.com/ajax/bootstrap/3.2.0/css/bootstrap.min.css>
        <link rel=stylesheet href=//ajax.aspnetcdn.com/ajax/bootstrap/3.2.0/css/bootstrap-theme.min.css>
        <meta name=viewport content="width=device-width, initial-scale=1">
    </head>
    <body>
        <div class=container>
            <h1>Creating a OneDrive folder</h1>
            <?php if (null !== $status) echo $status ?>
            <?php if (null !== $folder): ?>
            <h2>Properties</h2>
            <pre><?php print_r($folder->fetchProperties()) ?></pre>
            <?php endif ?>
            <p><a href=app.php>Back to the examples</a></p>
        </div>
    </body>
</html>
