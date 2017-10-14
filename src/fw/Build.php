<?php

namespace Framework;

class Build extends _Lib
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function build()
    {
        $build = new Build();
        $build->execute();
    }

    public function execute()
    {
        $dockerExtra = Psr11::container()->get('DOCKERFILE');
        $dockerExtra = array_merge(
            [
                '## START',
                'ENV APPLICATION_ENV=' . Psr11::environment()->getCurrentEnv()
            ],
            $dockerExtra,
            [
                '## END'
            ]
        );

        $dockerFile = file_get_contents($this->workdir . '/docker/Dockerfile');

        file_put_contents(
            $this->workdir . '/Dockerfile',
            str_replace('##---ENV-SPECIFICS-HERE', implode("\n", $dockerExtra), $dockerFile)
        );

        $beforeBuild = implode(" ", Psr11::container()->get('DOCKER_BEFORE_BUILD'));
        $deployCommand = Psr11::container()->get('DOCKER_DEPLOY_COMMAND');

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
