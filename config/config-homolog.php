<?php

use ByJG\Cache\Psr16\FileSystemCacheEngine;
use ByJG\Util\JwtKeySecret;

return [

    'CACHE_ROUTES' => function () {
        return new FileSystemCacheEngine();
    },

    'WEB_SERVER' => 'homolog',
    'API_SERVER' => "homolog",
    'JWT_SECRET' => function () {
        return new JwtKeySecret('super_secret_key');
    },

    'DBDRIVER_CONNECTION' => 'mysql://root:' . getenv('MYSQL_ROOT_PASSWORD') . '@mysql-dev/mydb',

];
