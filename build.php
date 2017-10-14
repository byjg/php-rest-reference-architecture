#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/_lib.php';

class Build extends _Lib
{
    public function __construct()
    {
        parent::__construct();
    }

    public function build()
    {
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

        $beforeBuild = implode(" ", \RestTemplate\Psr11::container()->get('DOCKER_BEFORE_BUILD'));
        $deployCommand = \RestTemplate\Psr11::container()->get('DOCKER_DEPLOY_COMMAND');

        $this->liveExecuteCommand([
            "docker stop " . $this->container,
            "docker rmi " . $this->image
        ]);

        // Before Build
        if (!empty($beforeBuild)) {
            $this->liveExecuteCommand($beforeBuild);
        }

        // Build
        $this->liveExecuteCommand("docker build -t " . $this->image . " . ");

        // Deploy
        $this->liveExecuteCommand($deployCommand);
    }
}

$build = new Build();
$build->build();
