<?php

namespace Test\Functional\Krizalys\Onedrive;

trait ConfigurationTrait
{
    private static $config;

    private static function getConfig($key)
    {
        if (self::$config === null) {
            $path = sprintf('%s/config.php', __DIR__);

            if (!file_exists($path)) {
                throw new \Exception(
                    'Configuration file not found.'
                        . " Please create a $path file from the sample provided."
                );
            }

            self::$config = require $path;
        }

        return self::$config[$key];
    }
}
