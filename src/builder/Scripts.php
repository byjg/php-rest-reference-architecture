<?php

namespace Builder;

use Composer\Script\Event;

class Scripts extends _Lib
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function build()
    {
        $build = new Scripts();
        $build->runBuild();
    }

    public static function migrate(Event $event)
    {
        $migrate = new Scripts();
        $migrate->runMigrate($event->getArguments());
    }

    public static function genRestDocs()
    {
        $build = new Scripts();
        $build->runGenRestDocs();
    }




    public function runBuild()
    {
        $dockerExtra = Psr11::container()->get('BUILDER_DOCKERFILE');
        if (!empty($dockerExtra)) {
            $dockerExtra = array_merge(
                [
                    '############################################################',
                    '##-- START CUSTOM',
                    'ENV APPLICATION_ENV=' . Psr11::environment()->getCurrentEnv()
                ],
                (array)$dockerExtra,
                [
                    '##-- END CUSTOM',
                    '############################################################',
                ]
            );

            $dockerFile = file_get_contents($this->workdir . '/docker/Dockerfile');

            file_put_contents(
                $this->workdir . '/Dockerfile',
                str_replace('##---ENV-SPECIFICS-HERE', implode("\n", $dockerExtra), $dockerFile)
            );
        }

        $beforeBuild = Psr11::container()->get('BUILDER_BEFORE_BUILD');
        $build = Psr11::container()->get('BUILDER_BUILD');
        $deployCommand = Psr11::container()->get('BUILDER_DEPLOY_COMMAND');
        $afterDeploy = Psr11::container()->get('BUILDER_AFTER_DEPLOY');

        // Before Build
        if (!empty($beforeBuild)) {
            $this->liveExecuteCommand($beforeBuild);
        }

        // Build
        if (!empty($build)) {
            $this->liveExecuteCommand($build);
        }
        // Deploy
        if (!empty($deployCommand)) {
            $this->liveExecuteCommand($deployCommand);
        }
        // After Deploy
        if (!empty($afterDeploy)) {
            $this->liveExecuteCommand($afterDeploy);
        }
    }


    public function runMigrate($arguments)
    {
        $dbConnection = Psr11::container()->get('DBDRIVER_CONNECTION');

        $params = implode(' ', $arguments);
        if (!empty($params)) {
            $params .= " \"$dbConnection\"";
        }

        chdir($this->workdir);
        $cmdLine = $this->fixDir("vendor/bin/migrate") . " -vvv --path=db $params";

        $this->liveExecuteCommand($cmdLine);
    }

    public function runGenRestDocs()
    {
        $docPath = $this->workdir . '/web/docs/';
        chdir($this->workdir);
        $this->liveExecuteCommand(
            $this->workdir . "/vendor/bin/swagger "
            . "--output \"$docPath\" "
            . "--exclude vendor "
            . "--exclude docker "
            . "--exclude fw "
            . "--processor OperationId"
        );

        $docs = file_get_contents("$docPath/swagger.json");
        $docs = str_replace('__HOSTNAME__', Psr11::container()->get('API_SERVER'), $docs);
        file_put_contents("$docPath/swagger.json", $docs);
    }
}
