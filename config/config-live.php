<?php

use ByJG\Util\JwtKeySecret;

return [
    'WEB_SERVER' => 'live',
    'API_SERVER' => "live",
    'JWT_SECRET' => function () {
        return new JwtKeySecret('super_secret_key');
    },

    'DBDRIVER_CONNECTION' => 'mysql://root:password@mysql-container/database',
];
