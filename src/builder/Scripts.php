<?php

namespace Builder;

use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\EnvironmentException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\DbMigration\Database\MySqlDatabase;
use ByJG\DbMigration\Exception\InvalidMigrationFile;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;
use Composer\Script\Event;
use Exception;
use Psr\SimpleCache\InvalidArgumentException;

class Scripts extends _Lib
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws KeyNotFoundException
     * @throws InvalidArgumentException
     */
    public static function dockerBuild()
    {
        $build = new Scripts();
        $build->execDockerBuild();
    }

    /**
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws KeyNotFoundException
     * @throws InvalidArgumentException
     */
    public static function dockerRun()
    {
        $build = new Scripts();
        $build->execDockerRun();
    }

    /**
     * @param Event $event
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws KeyNotFoundException
     * @throws InvalidMigrationFile
     * @throws InvalidArgumentException
     */
    public static function migrate(Event $event)
    {
        $migrate = new Scripts();
        $migrate->runMigrate($event->getArguments());
    }

    /**
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws KeyNotFoundException
     * @throws InvalidArgumentException
     */
    public static function genRestDocs()
    {
        $build = new Scripts();
        $build->runGenRestDocs();
    }


    /**
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws KeyNotFoundException
     * @throws InvalidArgumentException
     */
    public function execDockerBuild()
    {
        $build = Psr11::container()->get('BUILDER_DOCKER_BUILD');

        if (empty($build)) {
            return;
        }

        $this->liveExecuteCommand($build);
    }

    /**
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws KeyNotFoundException
     * @throws InvalidArgumentException
     */
    public function execDockerRun()
    {
        $deployCommand = Psr11::container()->get('BUILDER_DOCKER_RUN');

        if (empty($deployCommand)) {
            return;
        }

        $this->liveExecuteCommand($deployCommand);
    }


    /**
     * @param $arguments
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws KeyNotFoundException
     * @throws InvalidMigrationFile
     * @throws InvalidArgumentException
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
                throw new Exception("Reset require the argument --yes");
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
     * @param bool $hasCmd
     * @return array
     */
    protected function extractArguments($arguments, $hasCmd = true) {
        $ret = [
            '--up-to' => null,
            '--yes' => null,
            '--force' => false,
        ];

        $start = 0;
        if ($hasCmd) {
            $ret['command'] = isset($arguments[0]) ? $arguments[0] : null;
            $start = 1;
        }

        for ($i=$start; $i < count($arguments); $i++) {
            $args = explode("=", $arguments[$i]);
            $ret[$args[0]] = isset($args[1]) ? $args[1] : true;
        }

        return $ret;
    }

    /**
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws KeyNotFoundException
     * @throws InvalidArgumentException
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
