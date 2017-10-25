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
    }


    public function runMigrate($arguments)
    {
        $dbConnection = Psr11::container()->get('DBDRIVER_CONNECTION');

        $params = implode(' ', $arguments);
        if (!empty($params)) {
            $params .= " $dbConnection";
        }

        $cmdLine = $this->workdir . "/vendor/bin/migrate -vvv --path=\"%workdir%/db\" $params";

        $this->liveExecuteCommand($cmdLine);
    }

    public function runGenRestDocs()
    {
        $docPath = $this->workdir . '/web/docs/';
        $this->liveExecuteCommand(
            $this->workdir . "vendor/bin/swagger --output \"$docPath\" --exclude vendor,docker,fw"
        );

        $docs = file_get_contents("$docPath/swagger.json");
        $docs = str_replace('__HOSTNAME__', Psr11::container()->get('HOST'), $docs);
        file_put_contents("$docPath/swagger.json", $docs);
    }
}
