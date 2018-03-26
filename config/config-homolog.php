<?php

return [

    'CACHE_ROUTES' => function () {
        return new \ByJG\Cache\Psr16\FileSystemCacheEngine();
    },

    'WEB_SERVER' => 'homolog',
    'API_SERVER' => "homolog",
    'JWT_SECRET' => 'zteNpbuArRnv9+cGrZ2K2qn2b4tqgACg6NpxuVH1MHQ=',

    'DBDRIVER_CONNECTION' => 'sqlite://' . __DIR__ . '/../src/homolog.db',


    'BUILDER_DOCKERFILE' => [

    ],

    'BUILDER_BEFORE_BUILD' => [

    ],

    'BUILDER_DEPLOY_COMMAND' => [

    ],
];
