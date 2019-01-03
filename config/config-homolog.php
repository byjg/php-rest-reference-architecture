<?php

return [

    'CACHE_ROUTES' => function () {
        return new \ByJG\Cache\Psr16\FileSystemCacheEngine();
    },

    'WEB_SERVER' => 'homolog',
    'API_SERVER' => "homolog",
    'JWT_SECRET' => function () {
        return new \ByJG\Util\JwtKeySecret('zteNpbuArRnv9+cGrZ2K2qn2b4tqgACg6NpxuVH1MHQ=');
    },

    'DBDRIVER_CONNECTION' => 'mysql://root:password@mysql-container/database',

    'BUILDER_DOCKERFILE' => 'docker/Dockerfile',

    'BUILDER_DOCKER_BUILD' => [
        'docker build -t %image% . -f docker/Dockerfile',
    ],

    'BUILDER_DOCKER_RUN' => [
        'docker run -d --rm --name %container% '
        . '-e APPLICATION_ENV=%env% '
        . '--link mysql-container '
        . '-p "80:80" %image%',
    ],
];
