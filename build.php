#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/_lib.php';

$dockerExtra = \RestTemplate\Psr11::container()->get('DOCKERFILE');
$dockerExtra = array_merge(
    [
        '## START',
        'ENV APPLICATION_ENV=' . \RestTemplate\Psr11::environment()->getCurrentEnv()
    ],
    $dockerExtra,
    [
        '## END'
    ]
);

$dockerFile = file_get_contents(__DIR__ . '/docker/Dockerfile');

file_put_contents(
    __DIR__ . '/Dockerfile',
    str_replace('##---ENV-SPECIFICS-HERE', implode("\n", $dockerExtra), $dockerFile)
);

$image = 'resttemplate-' . \RestTemplate\Psr11::environment()->getCurrentEnv();
$container = "$image-instance";
$before = implode(" ", \RestTemplate\Psr11::container()->get('DOCKER_BEFORE_RUN'));
$cmdArgs = implode(" ", \RestTemplate\Psr11::container()->get('DOCKER_CMD_ARGS'));

liveExecuteCommand("docker stop $container");
liveExecuteCommand("docker rmi $image");
liveExecuteCommand("docker build -t $image . ");
if (!empty($before)) {
    liveExecuteCommand($before);
}
liveExecuteCommand("docker run -d --rm --name $container -p \"80:80\" $cmdArgs $image");
