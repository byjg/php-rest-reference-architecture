<?php

return [

    'API_SERVER' => '127.0.0.1',

    'BUILDER_DEPLOY_COMMAND' => [
        'docker run -d --rm --name %container% -v %workdir%:/srv/web -p "127.0.0.1:80:80" %image%',
    ],

];
