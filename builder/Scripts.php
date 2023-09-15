<?php

namespace Builder;

use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\InvalidDateException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\DbMigration\Database\MySqlDatabase;
use ByJG\DbMigration\Exception\InvalidMigrationFile;
use ByJG\DbMigration\Migration;
use ByJG\JinjaPhp\Loader\FileSystemLoader;
use ByJG\Util\Uri;
use Composer\Script\Event;
use Exception;
use OpenApi\Generator;
use Psr\SimpleCache\InvalidArgumentException;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use ReflectionException;
use RestTemplate\Psr11;

class Scripts extends BaseScripts
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Event $event
     * @return void
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws InvalidArgumentException
     * @throws InvalidDateException
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
     * @return void
     * @throws ConfigNotFoundException
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     */
    public static function genOpenApiDocs(Event $event)
    {
        $build = new Scripts();
        $build->runGenOpenApiDocs($event->getArguments());
    }

    /**
     * @param Event $event
     * @return void
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public static function codeGenerator(Event $event)
    {
        $build = new Scripts();
        $build->runCodeGenerator($event->getArguments());
    }

    /**
     * @param $arguments
     * @return void
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws InvalidArgumentException
     * @throws InvalidMigrationFile
     * @throws KeyNotFoundException
     * @throws ReflectionException
     * @throws ConfigException
     * @throws InvalidDateException
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

        Migration::registerDatabase(MySqlDatabase::class);

        $migration = new Migration(new Uri($dbConnection), $this->workdir . "/db");
        $migration->withTransactionEnabled(true);
        $migration->addCallbackProgress(function ($cmd, $version) {
            echo "Doing $cmd, $version\n";
        });

        $exec['reset'] = function () use ($migration, $argumentList) {
            if (!isset($argumentList["--yes"])) {
                throw new Exception("Reset require the argument '--yes'");
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
     * @param array $arguments
     * @param bool $hasCmd
     * @return array
     */
    protected function extractArguments(array $arguments, bool $hasCmd = true): array
    {
        $ret = [
            '--up-to' => null,
            '--yes' => null,
            '--force' => false,
            '--env' => null
        ];

        $start = 0;
        if ($hasCmd) {
            $ret['command'] = $arguments[0] ?? null;
            $start = 1;
        }

        for ($i=$start; $i < count($arguments); $i++) {
            $args = explode("=", $arguments[$i]);
            $ret[$args[0]] = $args[1] ?? true;
        }

        return $ret;
    }

    /**
     * @param array $arguments
     * @return void
     */
    public function runGenOpenApiDocs(array $arguments)
    {
        $docPath = $this->workdir . '/public/docs/';

        $generator = (new Generator())
            ->setConfig([
                "operationId.hash" => false
            ]);
        $openapi = $generator->generate(
            [
                $this->workdir . '/src',
            ]
        );
        file_put_contents("$docPath/openapi.json", $openapi->toJson());
    }

    /**
     * @param array $arguments
     * @return void
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws Exception
     */
    public function runCodeGenerator(array $arguments)
    {
        // Get Table Name
        $table = null;
        if (in_array("--table", $arguments)) {
            $index = array_search("--table", $arguments);
            $table = $arguments[$index + 1] ?? null;
            unset($arguments[$index + 1]);
            unset($arguments[$index]);
        }
        if (empty($table)) {
            throw new Exception("Table name is required (--table=table_name)");
        }

        // Check Arguments
        $foundArguments = [];
        $validArguments = ['model', 'repo', 'config' , 'rest', 'test', 'all', "--save", "--debug"];
        foreach ($arguments as $argument) {
            if (!in_array($argument, $validArguments)) {
                throw new Exception("Invalid argument: $argument\nValids are: " . implode(", ", $validArguments) . "\n");
            } else {
                $foundArguments[] = $argument;
            }
        }
        if (empty($foundArguments)) {
            throw new Exception("At least one argument is required (" . implode(", ", $validArguments) . ")");
        }
        $save = in_array("--save", $arguments);

        /** @var DbDriverInterface $dbDriver */
        $dbDriver = Psr11::container()->get(DbDriverInterface::class);

        $tableDefinition = $dbDriver->getIterator("EXPLAIN " . strtolower($table))->toArray();
        $tableIndexes = $dbDriver->getIterator("SHOW INDEX FROM " . strtolower($table))->toArray();

        // Convert DB Types to PHP Types
        foreach ($tableDefinition as $key => $field) {
            $type = preg_replace('/\(.*/', '', $field['type']);

            $tableDefinition[$key]['property'] = preg_replace_callback('/_(.?)/', function ($matches) {
                return strtoupper($matches[1]);
            }, $field['field']);

            switch ($type) {
                case 'int':
                case 'tinyint':
                case 'smallint':
                    $tableDefinition[$key]['php_type'] = 'int';
                    $tableDefinition[$key]['openapi_type'] = 'integer';
                    $tableDefinition[$key]['openapi_format'] = 'int32';
                    break;
                case 'mediumint':
                case 'bigint':
                case 'integer':
                    $tableDefinition[$key]['php_type'] = 'int';
                    $tableDefinition[$key]['openapi_type'] = 'integer';
                    $tableDefinition[$key]['openapi_format'] = 'int64';
                    break;
                case 'float':
                case 'double':
                case 'decimal':
                    $tableDefinition[$key]['php_type'] = 'float';
                    $tableDefinition[$key]['openapi_type'] = 'number';
                    $tableDefinition[$key]['openapi_format'] = 'double';
                    break;
                case 'bool':
                case 'boolean':
                    $tableDefinition[$key]['php_type'] = 'bool';
                    $tableDefinition[$key]['openapi_type'] = 'boolean';
                    $tableDefinition[$key]['openapi_format'] = 'boolean';
                    break;
                case 'date':
                    $tableDefinition[$key]['php_type'] = 'string';
                    $tableDefinition[$key]['openapi_type'] = 'string';
                    $tableDefinition[$key]['openapi_format'] = 'date';
                    break;
                case 'datetime':
                case 'timestamp':
                    $tableDefinition[$key]['php_type'] = 'string';
                    $tableDefinition[$key]['openapi_type'] = 'string';
                    $tableDefinition[$key]['openapi_format'] = 'date-time';
                    break;
                default:
                    $tableDefinition[$key]['php_type'] = 'string';
                    $tableDefinition[$key]['openapi_type'] = 'string';
                    $tableDefinition[$key]['openapi_format'] = 'string';
            }
        }

        // Create an array only with nullable fields
        $nullableFields = [];
        foreach ($tableDefinition as $field) {
            if ($field['null'] == 'YES') {
                $nullableFields[] = $field["property"];
            }
        }

        // Create an array only with primary keys
        $primaryKeys = [];
        foreach ($tableDefinition as $field) {
            if ($field['key'] == 'PRI') {
                $primaryKeys[] = $field["property"];
            }
        }

        // Create an array with non nullable fields but primary keys
        $nonNullableFields = [];
        foreach ($tableDefinition as $field) {
            if ($field['null'] == 'NO' && $field['key'] != 'PRI') {
                $nonNullableFields[] = $field["property"];
            }
        }

        // Create an array with non nullable fields but primary keys
        foreach ($tableIndexes as $key => $field) {
            $tableIndexes[$key]['camelColumnName'] = preg_replace_callback('/_(.?)/', function($match) {
                return strtoupper($match[1]);
            }, $field['column_name']);
        }

        $data = [
            'namespace' => 'RestTemplate',
            'restTag' => ucwords(explode('_', strtolower($table))[0]),
            'restPath' => str_replace('_', '/', strtolower($table)),
            'className' => preg_replace_callback('/(?:^|_)(.?)/', function($match) {
                return strtoupper($match[1]);
            }, $table),
            'varTableName' => preg_replace_callback('/_(.?)/', function ($matches) {
                return strtoupper($matches[1]);
            }, $table),
            'tableName' => strtolower($table),
            'fields' => $tableDefinition,
            'primaryKeys' => $primaryKeys,
            'nullableFields' => $nullableFields,
            'nonNullableFields' => $nonNullableFields,
            'indexes' => $tableIndexes,
        ];

        if (in_array("--debug", $arguments)) {
            print_r($data);
        }



        $loader = new FileSystemLoader(__DIR__ . '/../templates/codegen');

        if (in_array('all', $arguments) || in_array('model', $arguments)) {
            echo "Processing Model for table $table...\n";
            $template = $loader->getTemplate('model.php');
            if ($save) {
                $file = __DIR__ . '/../src/Model/' . $data['className'] . '.php';
                file_put_contents($file, $template->render($data));
                echo "File saved in $file\n";
            } else {
                print_r($template->render($data));
            }
        }

        if (in_array('all', $arguments) || in_array('repo', $arguments)) {
            echo "Processing Repository for table $table...\n";
            $template = $loader->getTemplate('repository.php');
            if ($save) {
                $file = __DIR__ . '/../src/Repository/' . $data['className'] . 'Repository.php';
                file_put_contents($file, $template->render($data));
                echo "File saved in $file\n";
            } else {
                print_r($template->render($data));
            }
        }

        if (in_array('all', $arguments) || in_array('rest', $arguments)) {
            echo "Processing Rest for table $table...\n";
            $template = $loader->getTemplate('rest.php');
            if ($save) {
                $file = __DIR__ . '/../src/Rest/' . $data['className'] . 'Rest.php';
                file_put_contents($file, $template->render($data));
                echo "File saved in $file\n";
            } else {
                print_r($template->render($data));
            }
        }

        if (in_array('all', $arguments) || in_array('test', $arguments)) {
            echo "Processing Test for table $table...\n";
            $template = $loader->getTemplate('test.php');
            if ($save) {
                $file = __DIR__ . '/../tests/Functional/Rest/' . $data['className'] . 'Test.php';
                file_put_contents($file, $template->render($data));
                echo "File saved in $file\n";
            } else {
                print_r($template->render($data));
            }
        }

        if (in_array('all', $arguments) || in_array('config', $arguments)) {
            echo "Processing Config for table $table...\n";
            $template = $loader->getTemplate('config.php');
            print_r($template->render($data));
        }
    }
}
