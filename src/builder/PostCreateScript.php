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

        $composerParts = explode("/", $composerName);

        //Replace composer name:
        $contents = file_get_contents($workdir . '/composer.json');
        file_put_contents(
            $workdir . '/composer.json',
            str_replace('byjg/resttemplate', $composerName, $contents)
        );

        // Replace Docker PHP Version
        $files = [ 'docker/Dockerfile', 'docker/Dockerfile-dev' ];
        foreach ($files as $file) {
            $contents = file_get_contents("$workdir/$file");
            $contents = str_replace('ENV TZ=America/Sao_Paulo', "ENV TZ=${timezone}", $contents);
            file_put_contents(
                "$workdir/$file",
                str_replace('FROM byjg/php:7.2-fpm-nginx', "FROM byjg/php:${phpVersion}-fpm-nginx", $contents)
            );
        }

        // Replace MySQL Connection
        $files = [ 'config/config-dev.php', 'config/config-homolog.php' , 'config/config-live.php', 'config/config-test.php', 'docker-compose.yml', 'bitbucket-pipelines.yml'];
        $uri = new Uri($mysqlConnection);
        foreach ($files as $file) {
            $contents = file_get_contents("$workdir/$file");
            $contents = str_replace( 'super_secret_key', JwtWrapper::generateSecret(64), $contents);
            $contents = str_replace('mysql://root:mysqlp455w0rd@mysql-container/mydb', "$mysqlConnection", $contents);
            $contents = str_replace('mysql-container', $uri->getHost(), $contents);
            $contents = str_replace('mysqlp455w0rd', $uri->getPassword(), $contents);
            $contents = str_replace('repository-name', $composerParts[1], $contents);
            file_put_contents(
                "$workdir/$file",
                $contents
            );
        }

        // Replace Namespace
        $objects = new RecursiveIteratorIterator($filter);
        foreach ($objects as $name => $object) {
            $contents = file_get_contents($name);
            if (strpos($contents, 'RestTemplate') !== false) {
                echo "$name\n";

                // Replace inside Quotes
                $contents = preg_replace(
                    "/([\'\"])RestTemplate(.*?[\'\"])/",
                    '$1' . str_replace('\\', '\\\\\\\\', $namespace) . '$2',
                    $contents
                );

                // Replace reserved name
                $contents = str_replace('RestTemplate', $namespace, $contents);

                // Replace reserved name
                $contents = str_replace(
                    'resttemplate',
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
        $workdir = realpath(__DIR__ . '/../..');
        $stdIo = $event->getIO();

        $stdIo->write("========================================================");
        $stdIo->write("  Setup RestTemplate");
        $stdIo->write("========================================================");
        $stdIo->write("");
        $stdIo->write("Project Directory: " . $workdir);
        $phpVersion = $stdIo->ask('PHP Version [7.2]: ', '7.2');
        $namespace = $stdIo->ask('Project namespace [MyRest]: ', 'MyRest');
        $composerName = $stdIo->ask('Composer name [me/myrest]: ', 'me/myrest');
        $mysqlConnection = $stdIo->ask('MySQL connection DEV [mysql://root:mysqlp455w0rd@mysql-container/mydb]: ', 'mysql://root:mysqlp455w0rd@mysql-container/mydb');
        $timezone = $stdIo->ask('Timezone [America/Sao_Paulo]: ', 'America/Sao_Paulo');
        $stdIo->ask('Press <ENTER> to continue');

        $script = new PostCreateScript();
        $script->execute($workdir, $namespace, $composerName, $phpVersion, $mysqlConnection, $timezone);
    }
}
