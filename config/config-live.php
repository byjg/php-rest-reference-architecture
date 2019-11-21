<?php

use ByJG\Util\JwtKeySecret;

return [
    'WEB_SERVER' => 'live',
    'API_SERVER' => "live",
    'JWT_SECRET' => function () {
        return new JwtKeySecret('super_secret_key');
    },

    'DBDRIVER_CONNECTION' => 'mysql://root:mysqlp455w0rd@mysql-container/mydb',
];
