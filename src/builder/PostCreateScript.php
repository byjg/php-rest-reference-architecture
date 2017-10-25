<?php

namespace Builder;

use Composer\Script\Event;

class PostCreateScript
{
    public function execute($workdir, $namespace, $composerName)
    {
        $directory = new \RecursiveDirectoryIterator($workdir);
        $filter = new \RecursiveCallbackFilterIterator($directory, function ($current/*, $key, $iterator*/) {
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


        //Replace composer name:
        $contents = file_get_contents($workdir . '/composer.json');
        file_put_contents(
            $workdir . '/composer.json',
            str_replace('byjg/resttemplate', $composerName, $contents)
        );

        $objects = new \RecursiveIteratorIterator($filter);
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
        $namespace = $stdIo->ask('Project namespace [MyRest]: ', 'MyRest');
        $composerName = $stdIo->ask('Composer name [me/myrest]: ', 'me/myrest');
        $stdIo->ask('Press <ENTER> to continue');

        $script = new PostCreateScript();
        $script->execute($workdir, $namespace, $composerName);
    }
}
