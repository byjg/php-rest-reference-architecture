<?php

return [

    'HOST' => 'homolog',

    'ROUTE_PATH_EXTRA' => [
        // Specific for the current environment.
    ],

    'JWT_SERVER' => "homolog",
    'JWT_SECRET' => 'zteNpbuArRnv9+cGrZ2K2qn2b4tqgACg6NpxuVH1MHQ=',


    'DBDRIVER_CONNECTION' => 'sqlite://' . __DIR__ . '/../src/homolog.db',


    'DOCKERFILE' => [
        'COPY config /srv/web/config',
        'COPY src /srv/web/src',
        'COPY vendor /srv/web/vendor',
        'COPY web /srv/web/web'
    ],
    'DOCKER_BEFORE_BUILD' => [

    ],
    'DOCKER_DEPLOY_COMMAND' => [

    ],
];
