<?php

use ByJG\Config\DependencyInjection as DI;
use ByJG\Util\JwtKeySecret;

return [

    'DBDRIVER_CONNECTION' => 'mysql://root:mysqlp455w0rd@mysql-container/mydb',

    JwtKeySecret::class => DI::bind(JwtKeySecret::class)
        ->withConstructorArgs(['jwt_super_secret_key'])
        ->toSingleton(),
];

