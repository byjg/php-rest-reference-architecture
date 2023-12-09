<?php

namespace Builder;

use ByJG\Util\JwtWrapper;
use ByJG\Util\Uri;
use Composer\Script\Event;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PostCreateScript
{
    public function execute($workdir, $namespace, $composerName, $phpVersion, $mysqlConnection, $timezone)
    {
        // ------------------------------------------------
        // Defining function to interatively walking through the directories
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
            $contents = str_replace('php:8.1-fpm', "php:$phpVersion-fpm", $contents);
            $contents = str_replace('php81', "php$phpVersionMSimple", $contents);
            file_put_contents(
                "$workdir/$file",
                $contents
            );
        }

        // ------------------------------------------------
        // Adjusting config files
        $files = [
            'config/config-dev.php',
            'config/config-staging.php' ,
            'config/config-prod.php',
            'config/config-test.php',
            'docker-compose-dev.yml',
            'docker-compose-image.yml'
        ];
        $uri = new Uri($mysqlConnection);
        foreach ($files as $file) {
            $contents = file_get_contents("$workdir/$file");
            $contents = str_replace( 'jwt_super_secret_key', JwtWrapper::generateSecret(64), $contents);
            $contents = str_replace('mysql://root:mysqlp455w0rd@mysql-container/mydb', "$mysqlConnection", $contents);
            $contents = str_replace('mysql-container', $uri->getHost(), $contents);
            $contents = str_replace('mysqlp455w0rd', $uri->getPassword(), $contents);
            $contents = str_replace('resttest', $composerParts[1], $contents);
            file_put_contents(
                "$workdir/$file",
                $contents
            );
        }

        // ------------------------------------------------
        // Adjusting namespace
        $objects = new RecursiveIteratorIterator($filter);
        foreach ($objects as $name => $object) {
            $contents = file_get_contents($name);
            if (strpos($contents, 'RestReferenceArchitecture') !== false) {
                echo "$name\n";

                // Replace inside Quotes
                $contents = preg_replace(
                    "/([\'\"])RestReferenceArchitecture(.*?[\'\"])/",
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
    }

    public static function run(Event $event)
    {
        $workdir = realpath(__DIR__ . '/..');
        $stdIo = $event->getIO();

        $currentPhpVersion = PHP_MAJOR_VERSION . "." .PHP_MINOR_VERSION;

        $stdIo->write("========================================================");
        $stdIo->write(" Setup Project");
        $stdIo->write(" Answer the questions below");
        $stdIo->write("========================================================");
        $stdIo->write("");
        $stdIo->write("Project Directory: " . $workdir);
        $phpVersion = $stdIo->ask("PHP Version [$currentPhpVersion]: ", $currentPhpVersion);
        $namespace = $stdIo->ask('Project namespace [MyRest]: ', 'MyRest');
        $composerName = $stdIo->ask('Composer name [me/myrest]: ', 'me/myrest');
        $mysqlConnection = $stdIo->ask('MySQL connection DEV [mysql://root:mysqlp455w0rd@mysql-container/mydb]: ', 'mysql://root:mysqlp455w0rd@mysql-container/mydb');
        $timezone = $stdIo->ask('Timezone [UTC]: ', 'UTC');
        $stdIo->ask('Press <ENTER> to continue');

        $script = new PostCreateScript();
        $script->execute($workdir, $namespace, $composerName, $phpVersion, $mysqlConnection, $timezone);
    }
}
