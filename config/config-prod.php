<?php

use ByJG\Config\DependencyInjection as DI;
use ByJG\Util\JwtKeySecret;

return [
    'WEB_SERVER' => 'www.example.org',
    'API_SERVER' => "api.example.org",
    'DBDRIVER_CONNECTION' => 'mysql://root:' . getenv('MYSQL_ROOT_PASSWORD') . '@mysql-prod/mydb',

    JwtKeySecret::class => DI::bind(JwtKeySecret::class)
        ->withConstructorArgs(['jwt_super_secret_key'])
        ->toSingleton(),
];
