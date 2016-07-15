<?php

require_once sprintf('%s/../../vendor/autoload.php', __DIR__);
require_once sprintf('%s/Krizalys/Onedrive/functions.php', __DIR__);

spl_autoload_register(function ($class) {
    if (0 === substr_compare($class, 'Test\\Mock\\', 0, 10)) {
        $suffix = substr($class, 10);
        $path   = str_replace('\\', DIRECTORY_SEPARATOR, $suffix) . '.php';
        require_once sprintf('%s/Mock/%s', __DIR__, $path);
    }
});
