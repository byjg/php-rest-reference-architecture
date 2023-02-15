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
use RestTemplate\Psr11;
use Exception;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

class Scripts extends BaseScripts
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Event $event
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws InvalidArgumentException
     * @throws InvalidMigrationFile
     * @throws KeyNotFoundException
     * @throws ReflectionException
     */
    public static function migrate(Event $event)
    {
        $migrate = new Scripts();
        $migrate->runMigrate($event->getArguments());
    }

    /**
     * @param Event $event
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     */
    public static function genRestDocs(Event $event)
    {
        $build = new Scripts();
        $build->runGenRestDocs($event->getArguments());
    }

    /**
     * @param $arguments
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws InvalidArgumentException
     * @throws InvalidMigrationFile
     * @throws KeyNotFoundException
     * @throws ReflectionException
     */
    public function runMigrate($arguments)
    {
        $argumentList = $this->extractArguments($arguments);
        if (isset($argumentList["command"])) {
            echo "> Command: " . $argumentList["command"] . "\n";
        } else {
            throw new Exception("Command not found. Use: reset, update, version");
        }

        $dbConnection = Psr11::container($argumentList["--env"])->get('DBDRIVER_CONNECTION');

        $migration = new Migration(new Uri($dbConnection), $this->workdir . "/db");
        $migration->registerDatabase("mysql", MySqlDatabase::class);
        $migration->addCallbackProgress(function ($cmd, $version) {
            echo "Doing $cmd, $version\n";
        });

        $exec['reset'] = function () use ($migration, $argumentList) {
            if (!isset($argumentList["yes"])) {
                throw new Exception("Reset require the argument 'yes'");
            }
            $migration->prepareEnvironment();
            $migration->reset();
        };


        $exec["update"] = function () use ($migration, $argumentList) {
            $migration->update($argumentList["--up-to"], $argumentList["--force"]);
        };

        $exec["version"] = function () use ($migration, $argumentList) {
            foreach ($migration->getCurrentVersion() as $key => $value) {
                echo "$key: $value\n";
            }
        };

        $exec[$argumentList['command']]();
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
            '--env' => null
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
     * @param $arguments
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     */
    public function runGenRestDocs($arguments)
    {
        $docPath = $this->workdir . '/public/docs/';

        $generator = (new \OpenApi\Generator())
            ->setConfig([
                "operationId.hash" => false
            ]);
        $openapi = $generator->generate(
            [
                $this->workdir . '/src',
            ],
            null,
            true
        );
        file_put_contents("$docPath/openapi.json", $openapi->toJson());
    }
}
