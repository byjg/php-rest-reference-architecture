<?php

namespace Builder;

use ByJG\DbMigration\Database\MySqlDatabase;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;
use Composer\Script\Event;
use RestTemplate\Psr11;

class Scripts extends BaseScripts
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \ByJG\Config\Exception\ConfigNotFoundException
     * @throws \ByJG\Config\Exception\EnvironmentException
     * @throws \ByJG\Config\Exception\KeyNotFoundException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function build()
    {
        $build = new Scripts();
        $build->runBuild();
    }

    /**
     * @param \Composer\Script\Event $event
     * @throws \ByJG\Config\Exception\ConfigNotFoundException
     * @throws \ByJG\Config\Exception\EnvironmentException
     * @throws \ByJG\Config\Exception\KeyNotFoundException
     * @throws \ByJG\DbMigration\Exception\InvalidMigrationFile
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function migrate(Event $event)
    {
        $migrate = new Scripts();
        $migrate->runMigrate($event->getArguments());
    }

    /**
     * @throws \ByJG\Config\Exception\ConfigNotFoundException
     * @throws \ByJG\Config\Exception\EnvironmentException
     * @throws \ByJG\Config\Exception\KeyNotFoundException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function genRestDocs()
    {
        $build = new Scripts();
        $build->runGenRestDocs();
    }


    /**
     * @throws \ByJG\Config\Exception\ConfigNotFoundException
     * @throws \ByJG\Config\Exception\EnvironmentException
     * @throws \ByJG\Config\Exception\KeyNotFoundException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function runBuild()
    {
        $dockerExtra = Psr11::container()->get('BUILDER_DOCKERFILE');
        if (!empty($dockerExtra)) {
            // @todo Analyse if hardcode the APPLIACATION_ENV
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


    /**
     * @param $arguments
     * @throws \ByJG\Config\Exception\ConfigNotFoundException
     * @throws \ByJG\Config\Exception\EnvironmentException
     * @throws \ByJG\Config\Exception\KeyNotFoundException
     * @throws \ByJG\DbMigration\Exception\InvalidMigrationFile
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function runMigrate($arguments)
    {
        $dbConnection = Psr11::container()->get('DBDRIVER_CONNECTION');

        $migration = new Migration(new Uri($dbConnection), $this->workdir . "/db");
        $migration->registerDatabase("mysql", MySqlDatabase::class);
        $migration->addCallbackProgress(function ($cmd, $version) {
            echo "Doing $cmd, $version\n";
        });

        $argumentList = $this->extractArguments($arguments);

        $exec['reset'] = function () use ($migration, $argumentList) {
            if (!$argumentList["--yes"]) {
                throw new \Exception("Reset require the argument --yes");
            }
            $migration->reset();
        };


        $exec["update"] = function () use ($migration, $argumentList) {
            $migration->update($argumentList["--up-to"], $argumentList["--force"]);
        };

        if (isset($exec[$argumentList['command']])) {
            $exec[$argumentList['command']]();
        }
    }

    /**
     * @param $arguments
     * @return array
     */
    protected function extractArguments($arguments) {
        $ret = [
            '--up-to' => null,
            '--yes' => null,
            '--force' => false,
        ];

        $ret['command'] = isset($arguments[0]) ? $arguments[0] : null;

        for ($i=1; $i < count($arguments); $i++) {
            $args = explode("=", $arguments[$i]);
            $ret[$args[0]] = isset($args[1]) ? $args[1] : true;
        }

        return $ret;
    }

    /**
     * @throws \ByJG\Config\Exception\ConfigNotFoundException
     * @throws \ByJG\Config\Exception\EnvironmentException
     * @throws \ByJG\Config\Exception\KeyNotFoundException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function runGenRestDocs()
    {
        $docPath = $this->workdir . '/web/docs/';
        chdir($this->workdir);
        $this->liveExecuteCommand(
            $this->fixDir("vendor/bin/swagger")
            . " --output \"$docPath\" "
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
