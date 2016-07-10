<?php
($config = include __DIR__ . '/../config.php') or die('Configuration file not found');
require_once __DIR__ . '/../vendor/autoload.php';

use Krizalys\Onedrive\Client;

try {
    // Instantiates a OneDrive client bound to your OneDrive application.
    $onedrive = new Client(array(
        'client_id' => $config['ONEDRIVE_CLIENT_ID'],
    ));

    // Gets a log in URL with sufficient privileges from the OneDrive API.
    $url = $onedrive->getLogInUrl(array(
        'wl.signin',
        'wl.basic',
        'wl.contacts_skydrive',
        'wl.skydrive_update',
    ), $config['ONEDRIVE_CALLBACK_URI']);

    session_start();

    // Persist the OneDrive client' state for next API requests.
    $_SESSION = array(
        'onedrive.client.state' => $onedrive->getState(),
    );
} catch (\Exception $e) {
    $status = sprintf('<p>Reason: <cite>%s</cite></p>', htmlspecialchars($e->getMesssage()));
    $url    = null;
}
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
            <?php if (null !== $status) echo $status ?>
            <?php if (null !== $url): ?>
            <div class=jumbotron>
                <p><a href="<?php echo htmlspecialchars($url) ?>" class="btn btn-primary btn-lg">Log in to OneDrive</a></p>
            </div>
            <?php endif ?>
        </div>
    </body>
</html>
