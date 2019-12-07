<?php

use ByJG\Util\JwtKeySecret;

return [
    'WEB_SERVER' => 'prod',
    'API_SERVER' => "prod",
    'JWT_SECRET' => function () {
        return new JwtKeySecret('super_secret_key');
    },

    'DBDRIVER_CONNECTION' => 'mysql://root:' . getenv('MYSQL_ROOT_PASSWORD') . '@mysql-prod/mydb',
];
