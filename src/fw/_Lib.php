<?php

namespace Framework;

class _Lib
{
    protected $container;
    protected $image;
    protected $workdir;

    public function __construct()
    {
        $this->image = $this->replaceVariables(Psr11::container()->getClosure('DOCKER_IMAGE'));
        $this->container = $this->image . "-instance";
        $this->workdir = realpath(__DIR__ . '/../..');
    }

    /**
     * Execute the given command by displaying console output live to the user.
     *  @param  string|array  $cmd          :  command to be executed
     *  @return array   exit_status  :  exit status of the executed command
     *                  output       :  console output of the executed command
     */
    protected function liveExecuteCommand($cmd)
    {
        // while (@ ob_end_flush()); // end all output buffers if any

        if (is_array($cmd)) {
            foreach ($cmd as $item) {
                $this->liveExecuteCommand($item);
            }
            return null;
        }

        $cmd = $this->replaceVariables($cmd);
        echo "\n>> $cmd\n";
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

        $exitStatus = intval($matches[0]);
        // if ($exitStatus !== 0) {
        //     exit($exitStatus);
        // }

        // return exit status and intended output
        return array (
            'exit_status'  => $exitStatus,
            'output'       => str_replace("Exit status : " . $matches[0], '', $completeOutput)
        );
    }

    protected $dockerVariables = null;
    protected function getDockerVariables()
    {
        if ($this->dockerVariables === null) {
            $this->dockerVariables = Psr11::container()->get('DOCKER_VARIABLES');
            if ($this->dockerVariables === null) {
                $this->dockerVariables = [];
            }
        }

        return $this->dockerVariables;
    }

    protected function replaceVariables($string)
    {
        // Deploy
        $args = [
            '%env%' => Psr11::environment()->getCurrentEnv(),
            '%workdir%' => $this->workdir,
            '%container%' => $this->container,
            '%image%' => $this->image
        ];

        $variables = $this->getDockerVariables();
        foreach ((array)$variables as $variable => $value) {
            $args["%$variable%"] = $value;
        }

        foreach ($args as $arg => $value) {
            $string = str_replace($arg, $value, $string);
        }

        return $string;
    }
}
