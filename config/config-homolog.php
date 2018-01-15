<?php

return [

    'HOST' => 'homolog',

    'CACHE_ROUTES' => function () {
        return new \ByJG\Cache\Psr16\FileSystemCacheEngine();
    },

    'JWT_SERVER' => "homolog",
    'JWT_SECRET' => 'zteNpbuArRnv9+cGrZ2K2qn2b4tqgACg6NpxuVH1MHQ=',


    'DBDRIVER_CONNECTION' => 'sqlite://' . __DIR__ . '/../src/homolog.db',


    'BUILDER_DOCKERFILE' => [
        'COPY config /srv/web/config',
        'COPY src /srv/web/src',
        'COPY vendor /srv/web/vendor',
        'COPY web /srv/web/web'
    ],
    'BUILDER_BEFORE_BUILD' => [

    ],
    'BUILDER_DEPLOY_COMMAND' => [

    ],
];
