<?php

require_once __DIR__ . '/../../onedrive.php';
@session_start();

if (!array_key_exists('onedrive.client.state', $_SESSION)) {
    exit(0);
}

$state = $_SESSION['onedrive.client.state'];

$onedrive = new \Onedrive\Client([
    'state' => $state,
]);

$quota = $onedrive->fetchQuota();
@header('Content-Type: application/json', true);

echo json_encode($quota, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
