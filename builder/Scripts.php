<?php

namespace Builder;

use ByJG\AnyDataset\Db\DatabaseExecutor;
use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\InvalidDateException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\DbMigration\Database\MySqlDatabase;
use ByJG\DbMigration\Exception\InvalidMigrationFile;
use ByJG\DbMigration\Migration;
use ByJG\JinjaPhp\Exception\TemplateParseException;
use ByJG\JinjaPhp\Loader\FileSystemLoader;
use ByJG\Util\Uri;
use Composer\Script\Event;
use Exception;
use OpenApi\Generator;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use RestReferenceArchitecture\Psr11;

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
    public static function migrate(Event $event): void
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
    public static function genOpenApiDocs(Event $event): void
    {
        $build = new Scripts();
        $build->runGenOpenApiDocs($event->getArguments());
    }

    /**
     * @param Event $event
     * @return void
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws InvalidArgumentException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     * @throws TemplateParseException
     */
    public static function codeGenerator(Event $event): void
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
     * @throws Exception
     */
    public function runMigrate($arguments): void
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
    public function runGenOpenApiDocs(array $arguments): void
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
     * Get code generator usage help text
     *
     * @return string
     */
    protected function getCodeGeneratorHelp(): string
    {
        return "Usage:\n" .
            "  composer codegen -- --table=<table_name> <arguments> [options]\n\n" .
            "Required:\n" .
            "  --table=<name>        Database table name\n\n" .
            "Arguments (at least one required):\n" .
            "  all                   Generate all components for the selected pattern\n" .
            "  model                 Generate Model\n" .
            "  repo|repository       Generate Repository (Repository pattern only)\n" .
            "  service               Generate Service (Repository pattern only)\n" .
            "  rest                  Generate REST controller\n" .
            "  test                  Generate Test\n\n" .
            "Options:\n" .
            "  --activerecord        Use ActiveRecord pattern instead of Repository pattern\n" .
            "  --env=<environment>   Environment (dev, test, prod)\n" .
            "  --save                Save generated files to disk\n" .
            "  --debug               Show debug information\n\n" .
            "Examples:\n" .
            "  # Repository pattern (default) - generates Model, Repository, Service, Rest, Test\n" .
            "  composer codegen -- --table=users all --save\n\n" .
            "  # ActiveRecord pattern - generates Model (with ActiveRecord trait), Rest, Test\n" .
            "  composer codegen -- --table=users all --activerecord --save\n\n" .
            "  # Generate only specific components\n" .
            "  composer codegen -- --table=users model rest --save\n" .
            "  composer codegen -- --table=users model --activerecord --save\n\n" .
            "  # Preview without saving\n" .
            "  composer codegen -- --table=users all --activerecord\n";
    }

    /**
     * @param array $arguments
     * @return void
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws InvalidArgumentException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     * @throws TemplateParseException
     * @throws Exception
     */
    public function runCodeGenerator(array $arguments): void
    {
        // Get Table Name - support both --table=value and --table value formats
        $table = null;
        foreach ($arguments as $index => $arg) {
            if (str_starts_with($arg, "--table=")) {
                $table = substr($arg, 8); // Extract value after --table=
                unset($arguments[$index]);
                break;
            } elseif ($arg === "--table") {
                $table = $arguments[$index + 1] ?? null;
                unset($arguments[$index + 1]);
                unset($arguments[$index]);
                break;
            }
        }
        // Reindex array after unsetting elements
        $arguments = array_values($arguments);

        if (empty($table)) {
            throw new Exception("Table name is required.\n\n" . $this->getCodeGeneratorHelp());
        }

        // Extract --env parameter
        $argumentList = $this->extractArguments($arguments, false);
        $env = $argumentList['--env'] ?? null;

        // Extract --activerecord flag
        $isActiveRecord = in_array("--activerecord", $arguments);

        // Check Arguments
        $foundArguments = [];
        $validArguments = ['model', 'repo', 'repository', 'service', 'rest', 'test', 'all', "--save", "--debug", "--env", "--activerecord", "--table"];
        foreach ($arguments as $argument) {
            // Skip --env=value and --table=value formats
            if (str_starts_with($argument, "--env=") || str_starts_with($argument, "--table=")) {
                continue;
            }
            if (!in_array($argument, $validArguments)) {
                throw new Exception("Invalid argument: $argument\n\n" . $this->getCodeGeneratorHelp());
            } else {
                $foundArguments[] = $argument;
            }
        }
        if (empty($foundArguments)) {
            throw new Exception("At least one argument is required.\n\n" . $this->getCodeGeneratorHelp());
        }
        $save = in_array("--save", $arguments);

        /** @var DatabaseExecutor $executor */
        $executor = Psr11::container($env)->get(DatabaseExecutor::class);

        $tableDefinition = $executor->getIterator("EXPLAIN " . strtolower($table))->toArray();
        $tableIndexes = $executor->getIterator("SHOW INDEX FROM " . strtolower($table))->toArray();
        $autoIncrement = false;

        // Convert DB Types to PHP Types
        foreach ($tableDefinition as $key => $field) {
            $type = preg_replace('/\(.*/', '', $field['type']);

            $tableDefinition[$key]['property'] = preg_replace_callback('/_(.?)/', function ($matches) {
                return strtoupper($matches[1]);
            }, $field['field']);

            if ($field['extra'] == 'auto_increment') {
                $autoIncrement = true;
            }

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

        // Create an array with non-nullable fields but primary keys
        $nonNullableFields = [];
        foreach ($tableDefinition as $field) {
            if ($field['null'] == 'NO' && $field['key'] != 'PRI') {
                $nonNullableFields[] = $field["property"];
            }
        }

        // Create an array with non-nullable fields but primary keys
        foreach ($tableIndexes as $key => $field) {
            $tableIndexes[$key]['camelColumnName'] = preg_replace_callback('/_(.?)/', function($match) {
                return strtoupper($match[1]);
            }, $field['column_name']);
        }

        // Detect timestamp fields for trait usage
        $hasCreatedAt = false;
        $hasUpdatedAt = false;
        $hasDeletedAt = false;
        foreach ($tableDefinition as $field) {
            if ($field['field'] == 'created_at') {
                $hasCreatedAt = true;
            }
            if ($field['field'] == 'updated_at') {
                $hasUpdatedAt = true;
            }
            if ($field['field'] == 'deleted_at') {
                $hasDeletedAt = true;
            }
        }

        $data = [
            'namespace' => 'RestReferenceArchitecture',
            'autoIncrement' => $autoIncrement ? 'yes' : 'no',
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
            'activerecord' => $isActiveRecord,
            'hasCreatedAt' => $hasCreatedAt,
            'hasUpdatedAt' => $hasUpdatedAt,
            'hasDeletedAt' => $hasDeletedAt,
        ];

        if (in_array("--debug", $arguments)) {
            print_r($data);
        }



        $loader = new FileSystemLoader(__DIR__ . '/../templates/codegen');

        if (in_array('all', $arguments) || in_array('model', $arguments)) {
            $modelType = $isActiveRecord ? "ActiveRecord Model" : "Model";
            echo "Processing $modelType for table $table...\n";
            $template = $loader->getTemplate('model.php');
            if ($save) {
                $file = __DIR__ . '/../src/Model/' . $data['className'] . '.php';
                file_put_contents($file, $template->render($data));
                echo "File saved in $file\n";
            } else {
                print_r($template->render($data));
            }
        }

        // Repository - only for Repository pattern (skip for ActiveRecord)
        if (!$isActiveRecord && (in_array('all', $arguments) || in_array('repo', $arguments) || in_array('repository', $arguments))) {
            echo "Processing Repository for table $table...\n";
            $template = $loader->getTemplate('repository.php');
            if ($save) {
                $file = __DIR__ . '/../src/Repository/' . $data['className'] . 'Repository.php';
                file_put_contents($file, $template->render($data));
                echo "File saved in $file\n";

                // Add to config if not exists
                $this->addToConfig(
                    __DIR__ . '/../config/dev/04-repositories.php',
                    $data['className'] . 'Repository',
                    $data['namespace']
                );
            } else {
                print_r($template->render($data));
            }
        }

        // Service - only for Repository pattern (skip for ActiveRecord)
        if (!$isActiveRecord && (in_array('all', $arguments) || in_array('service', $arguments))) {
            echo "Processing Service for table $table...\n";
            $template = $loader->getTemplate('service.php');
            if ($save) {
                $file = __DIR__ . '/../src/Service/' . $data['className'] . 'Service.php';
                file_put_contents($file, $template->render($data));
                echo "File saved in $file\n";

                // Add to config if not exists
                $this->addToConfig(
                    __DIR__ . '/../config/dev/05-services.php',
                    $data['className'] . 'Service',
                    $data['namespace']
                );
            } else {
                print_r($template->render($data));
            }
        }

        if (in_array('all', $arguments) || in_array('rest', $arguments)) {
            $restType = $isActiveRecord ? "ActiveRecord Rest" : "Rest";
            $templateName = $isActiveRecord ? 'restactiverecord.php' : 'rest.php';
            echo "Processing $restType for table $table...\n";
            $template = $loader->getTemplate($templateName);
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
                $file = __DIR__ . '/../tests/Rest/' . $data['className'] . 'Test.php';
                file_put_contents($file, $template->render($data));
                echo "File saved in $file\n";
            } else {
                print_r($template->render($data));
            }
        }
    }

    /**
     * Add DI binding to config file if it doesn't exist
     *
     * @param string $configFile Path to the config file
     * @param string $className Class name (without namespace, e.g., 'DummyRepository')
     * @param string $namespace Namespace (e.g., 'RestReferenceArchitecture')
     * @return void
     */
    protected function addToConfig(string $configFile, string $className, string $namespace): void
    {
        $contents = file_get_contents($configFile);
        $modified = false;

        // Determine the type (Repository or Service) based on class name
        $type = str_ends_with($className, 'Repository') ? 'Repository' : 'Service';
        $fullClassName = "$namespace\\{$type}\\$className";
        $useStatement = "use $fullClassName;";

        // Check if use statement already exists
        if (!str_contains($contents, $useStatement)) {
            // Find the last use statement and add after it
            $lines = explode("\n", $contents);
            $lastUseLine = 0;

            foreach ($lines as $index => $line) {
                if (preg_match('/^use\s+.*?;/', trim($line))) {
                    $lastUseLine = $index;
                }
            }

            // Insert the new use statement after the last use statement
            array_splice($lines, $lastUseLine + 1, 0, $useStatement);
            $contents = implode("\n", $lines);
            $modified = true;
            echo "Added use statement for $className to " . basename($configFile) . "\n";
        }

        // Check if DI binding already exists
        if (!str_contains($contents, "$className::class")) {
            // Create the DI binding
            $binding = "\n    $className::class => DI::bind($className::class)\n" .
                       "        ->withInjectedConstructor()\n" .
                       "        ->toSingleton(),\n";

            // Find the position before the closing ];
            $pos = strrpos($contents, '];');
            if ($pos !== false) {
                $contents = substr_replace($contents, $binding, $pos, 0);
                $modified = true;
                echo "Added DI binding for $className to " . basename($configFile) . "\n";
            }
        }

        if ($modified) {
            file_put_contents($configFile, $contents);
        } else {
            echo "$className already exists in " . basename($configFile) . "\n";
        }
    }
}
