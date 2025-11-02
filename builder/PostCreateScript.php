<?php

namespace Builder;

use ByJG\AnyDataset\Db\Factory;
use ByJG\JwtWrapper\JwtWrapper;
use ByJG\Util\Uri;
use Composer\Script\Event;
use Exception;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PostCreateScript
{
    public function execute($workdir, $namespace, $composerName, $phpVersion, $mysqlConnection, $timezone): void
    {
        // ------------------------------------------------
        // Defining function to interactively walking through the directories
        $directory = new RecursiveDirectoryIterator($workdir);
        $filter = new RecursiveCallbackFilterIterator($directory, function ($current/*, $key, $iterator*/) {
            // Skip hidden files and directories.
            if ($current->getFilename()[0] === '.') {
                return false;
            }
            if ($current->isDir()) {
                // Only recurse into intended subdirectories.
                return $current->getFilename() !== 'fw';
            }
            // else {
            //     // Only consume files of interest.
            //     return strpos($current->getFilename(), 'wanted_filename') === 0;
            // }
            return true;
        });

        $composerName = strtolower($composerName);
        $composerParts = explode("/", $composerName);
        $phpVersionMSimple = str_replace(".", "", $phpVersion);

        // ------------------------------------------------
        //Replace composer name:
        $contents = file_get_contents($workdir . '/composer.json');
        file_put_contents(
            $workdir . '/composer.json',
            str_replace('byjg/rest-reference-architecture', $composerName, $contents)
        );

        // ------------------------------------------------
        // Replace Docker PHP Version
        $files = [ 'docker/Dockerfile' ];
        foreach ($files as $file) {
            $contents = file_get_contents("$workdir/$file");
            $contents = str_replace('ENV TZ=UTC', "ENV TZ=$timezone", $contents);
            $contents = str_replace('php:8.3-fpm', "php:$phpVersion-fpm", $contents);
            $contents = str_replace('php83', "php$phpVersionMSimple", $contents);
            file_put_contents(
                "$workdir/$file",
                $contents
            );
        }

        // ------------------------------------------------
        // Adjusting config files
        $files = [
            'config/dev/credentials.env',
            'config/test/credentials.env',
            'config/staging/credentials.env',
            'config/prod/credentials.env',
            'docker-compose-dev.yml',
        ];
        $uri = new Uri($mysqlConnection);
        foreach ($files as $file) {
            $contents = file_get_contents("$workdir/$file");

            // Common replacements for all files
            $contents = str_replace('mysql://root:mysqlp455w0rd@mysql-container/mydb', "$mysqlConnection", $contents);
            $contents = str_replace('mysql-container', $uri->getHost(), $contents);
            $contents = str_replace('mysqlp455w0rd', $uri->getPassword(), $contents);
            $contents = str_replace('resttest', $composerParts[1], $contents);

            // JWT_SECRET only for .env files - each gets unique secret
            if (str_ends_with($file, '.env')) {
                $jwtSecret = JwtWrapper::generateSecret(64);
                $contents = preg_replace('/JWT_SECRET=.*/', "JWT_SECRET=$jwtSecret", $contents);
            }

            file_put_contents("$workdir/$file", $contents);
        }

        // ------------------------------------------------
        // Adjusting namespace
        $objects = new RecursiveIteratorIterator($filter);
        foreach ($objects as $name => $object) {
            $contents = file_get_contents($name);
            if (str_contains($contents, 'RestReferenceArchitecture')) {
                echo "$name\n";

                // Replace inside Quotes
                $contents = preg_replace(
                    "/(['\"])RestReferenceArchitecture(.*?['\"])/",
                    '$1' . str_replace('\\', '\\\\\\\\', $namespace) . '$2',
                    $contents
                );

                // Replace reserved name
                $contents = str_replace('RestReferenceArchitecture', $namespace, $contents);

                // Replace reserved name
                $contents = str_replace(
                    'rest-reference-architecture',
                    str_replace('/', '', $composerName),
                    $contents
                );

                // Save it
                file_put_contents(
                    $name,
                    $contents
                );
            }
        }

        shell_exec("composer update");
        shell_exec("git init");
        shell_exec("git branch -m main");
        shell_exec("git add .");
        shell_exec("git commit -m 'Initial commit'");
    }

    /**
     * @param Event $event
     * @return void
     * @throws Exception
     */
    public static function run(Event $event)
    {
        $workdir = realpath(__DIR__ . '/..');
        $stdIo = $event->getIO();

        $currentPhpVersion = PHP_MAJOR_VERSION . "." .PHP_MINOR_VERSION;

        $validatePHPVersion = function ($arg) {
            $validPHPVersions = ['8.1', '8.2', '8.3'];
            if (in_array($arg, $validPHPVersions)) {
                return $arg;
            }
            throw new Exception('Only the PHP versions ' . implode(', ', $validPHPVersions) . ' are supported');
        };

        $validateNamespace = function ($arg) {
            if (empty($arg) || !preg_match('/^[A-Z][a-zA-Z0-9]*$/', $arg)) {
                throw new Exception('Namespace must be one word in CamelCase');
            }
            return $arg;
        };

        $validateComposer = function ($arg) {
            if (empty($arg) || !preg_match('/^[a-z0-9-]+\/[a-z0-9-]+$/', $arg)) {
                throw new Exception('Invalid Composer name');
            }
            return $arg;
        };

        $validateURI = function ($arg) {
            $uri = new Uri($arg);
            if (empty($uri->getScheme())) {
                throw new Exception('Invalid URI');
            }
            Factory::getRegisteredDrivers($uri->getScheme());
            return $arg;
        };

        $validateTimeZone = function ($arg) {
            if (empty($arg) || !in_array($arg, timezone_identifiers_list())) {
                throw new Exception('Invalid Timezone');
            }
            return $arg;
        };

        $maxRetries = 5;

        $stdIo->write("========================================================");
        $stdIo->write(" Setup Project");
        $stdIo->write(" Answer the questions below");
        $stdIo->write("========================================================");
        $stdIo->write("");
        $stdIo->write("Project Directory: " . $workdir);
        $phpVersion = $stdIo->askAndValidate("PHP Version [$currentPhpVersion]: ", $validatePHPVersion, $maxRetries, $currentPhpVersion);
        $namespace = $stdIo->askAndValidate('Project namespace [MyRest]: ', $validateNamespace, $maxRetries, 'MyRest');
        $composerName = $stdIo->askAndValidate('Composer name [me/myrest]: ', $validateComposer, $maxRetries, 'me/myrest');
        $mysqlConnection = $stdIo->askAndValidate('MySQL connection DEV [mysql://root:mysqlp455w0rd@mysql-container/mydb]: ', $validateURI, $maxRetries, 'mysql://root:mysqlp455w0rd@mysql-container/mydb');
        $timezone = $stdIo->askAndValidate('Timezone [UTC]: ', $validateTimeZone, $maxRetries, 'UTC');
        $stdIo->ask('Press <ENTER> to continue');

        $script = new PostCreateScript();
        $script->execute($workdir, $namespace, $composerName, $phpVersion, $mysqlConnection, $timezone);
    }
}
