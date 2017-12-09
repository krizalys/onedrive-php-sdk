<?php

function route()
{
    $uri        = $_SERVER['REQUEST_URI'];
    $components = parse_url($uri);
    $path       = array_key_exists('path', $components) ? $components['path'] : '';

    if ($path == '/') {
        if (!array_key_exists('query', $components)) {
            throw new Exception('code not given');
        }

        parse_str($components['query'], $query);
        $code   = $query['code'];
        $stdout = fopen('php://stdout', 'w');
        fwrite($stdout, $code);
        fclose($stdout);
    }

    return true;
}

return route();
