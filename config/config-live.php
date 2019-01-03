<?php

return [
    'WEB_SERVER' => 'live',
    'API_SERVER' => "live",
    'JWT_SECRET' => function () {
        return new \ByJG\Util\JwtKeySecret('CxSBhq0AMI9otIiqtc7w3kxqZ21D1dfACLPi1S1r8p74z8gQLu4HJb1H6KuivKn3RXNR9oZat98GViVGGUzcpQ==');
    },

    'DBDRIVER_CONNECTION' => 'mysql://root:password@mysql-container/database',
];
