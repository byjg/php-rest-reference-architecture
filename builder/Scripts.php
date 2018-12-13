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
    public static function dockerBuild()
    {
        $build = new Scripts();
        $build->execDockerBuild();
    }

    /**
     * @throws \ByJG\Config\Exception\ConfigNotFoundException
     * @throws \ByJG\Config\Exception\EnvironmentException
     * @throws \ByJG\Config\Exception\KeyNotFoundException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function dockerRun()
    {
        $build = new Scripts();
        $build->execDockerRun();
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
    public function execDockerBuild()
    {
        $dockerFile = Psr11::container()->get('BUILDER_DOCKERFILE');

        $build = Psr11::container()->get('BUILDER_DOCKER_BUILD');

        // Build
        if (!empty($build)) {
            $this->liveExecuteCommand($build);
        }
    }

    /**
     * @throws \ByJG\Config\Exception\ConfigNotFoundException
     * @throws \ByJG\Config\Exception\EnvironmentException
     * @throws \ByJG\Config\Exception\KeyNotFoundException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function execDockerRun()
    {
        $deployCommand = Psr11::container()->get('BUILDER_DOCKER_RUN');

        // Deploy
        if (!empty($deployCommand)) {
            $this->liveExecuteCommand($deployCommand);
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
            $migration->prepareEnvironment();
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
