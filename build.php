#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

$dockerExtra = \RestTemplate\Psr11::container()->get('DOCKERFILE');
$dockerExtra = array_merge(
    [
        '## START',
        'ENV APPLICATION_ENV=' . \RestTemplate\Psr11::environment()->getCurrentEnv()
    ],
    $dockerExtra,
    [
        '## END'
    ]
);

$dockerFile = file_get_contents(__DIR__ . '/docker/Dockerfile');

file_put_contents(
    __DIR__ . '/Dockerfile',
    str_replace('##---ENV-SPECIFICS-HERE', implode("\n", $dockerExtra), $dockerFile)
);

$image = 'resttemplate-' . \RestTemplate\Psr11::environment()->getCurrentEnv();
$container = "$image-instance";
$before = implode(" ", \RestTemplate\Psr11::container()->get('DOCKER_BEFORE_RUN'));
$cmdArgs = implode(" ", \RestTemplate\Psr11::container()->get('DOCKER_CMD_ARGS'));

liveExecuteCommand("docker stop $container");
liveExecuteCommand("docker rmi $image");
liveExecuteCommand("docker build -t $image . ");
if (!empty($before)) {
    liveExecuteCommand($before);
}
liveExecuteCommand("docker run -d --rm --name $container -p \"80:80\" $cmdArgs $image");



/**
 * Execute the given command by displaying console output live to the user.
 *  @param  string  cmd          :  command to be executed
 *  @return array   exit_status  :  exit status of the executed command
 *                  output       :  console output of the executed command
 */
function liveExecuteCommand($cmd)
{
    // while (@ ob_end_flush()); // end all output buffers if any

    $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

    $completeOutput = "";

    while (!feof($proc)) {
        $liveOutput     = fread($proc, 4096);
        $completeOutput = $completeOutput . $liveOutput;
        echo "$liveOutput";
        @ flush();
    }

    pclose($proc);

    // get exit status
    preg_match('/[0-9]+$/', $completeOutput, $matches);

    // return exit status and intended output
    return array (
        'exit_status'  => intval($matches[0]),
        'output'       => str_replace("Exit status : " . $matches[0], '', $completeOutput)
    );
}
