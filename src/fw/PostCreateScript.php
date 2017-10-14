<?php

namespace Framework;

use Composer\Script\Event;

class PostCreateScript
{
    public function execute($namespace, $composerName)
    {
        $workdir = realpath(__DIR__ . '/../..');
        
        $directory = new \RecursiveDirectoryIterator($workdir);
        $filter = new \RecursiveCallbackFilterIterator($directory, function ($current, $key, $iterator) {
            // Skip hidden files and directories.
            if ($current->getFilename()[0] === '.') {
                return FALSE;
            }
            if ($current->isDir()) {
                // Only recurse into intended subdirectories.
                return $current->getFilename() !== 'fw';
            }
            // else {
            //     // Only consume files of interest.
            //     return strpos($current->getFilename(), 'wanted_filename') === 0;
            // }
            return TRUE;
        });


        //Replace composer name:
        $contents = file_get_contents($workdir . '/composer.json');
        file_put_contents(
            $workdir . '/composer.json',
            str_replace('byjg/resttemplate', $composerName, $contents)
        );

        $objects = new \RecursiveIteratorIterator($filter);
        foreach($objects as $name => $object){
            $contents = file_get_contents($name);
            if (strpos($contents, 'RestTemplate') !== false) {
                echo "$name\n";
                file_put_contents(
                    $name,
                    str_replace('RestTemplate', $namespace, $contents)
                );
            }
        }
    }

    public static function run(Event $event)
    {
        $io = $event->getIO();

        $io->write("========================================================");
        $io->write("  Setup RestTemplate");
        $io->write("========================================================");
        $io->write("");
        $namespace = $io->ask('Project namespace [MyRest]: ', 'MyRest');
        $composerName = $io->ask('Composer name [me/myrest]: ', 'me/myrest');
        $io->ask('Press <ENTER> to continue');

        $script = new PostCreateScript();
        $script->execute($namespace, $composerName);
    }
}

