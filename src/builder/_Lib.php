<?php

namespace Builder;

class _Lib
{
    protected $container;
    protected $image;
    protected $workdir;
    protected $os;

    public function __construct()
    {
        $this->workdir = realpath(__DIR__ . '/../..');
    }

    public function getOs()
    {
        if (!$this->os) {
            $this->os = php_uname('s');
            if (preg_match('/[Ww]in/', $this->os)) {
                $this->os = 'Windows';
            }
        }

        return $this->os;
    }

    public function fixDir($command)
    {
        if ($this->getOs() === "Windows") {
            return str_replace('/', '\\', $command);
        }
        return $command;
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

        $complement = " 2>&1 ; echo Exit status : $?";
        if ($this->getOs() === "Windows") {
            $complement = ' & echo Exit status : %errorlevel%';
        }
        $proc = popen("$cmd $complement", 'r');

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
        // Builder Variables
        if ($this->dockerVariables === null) {
            $this->dockerVariables = [
                '%env%'     => Psr11::environment()->getCurrentEnv(),
                '%workdir%' => $this->workdir
            ];

            // Get User Variables
            $variables = Psr11::container()->get('BUILDER_VARIABLES');
            foreach ((array)$variables as $variable => $value) {
                $this->dockerVariables["%$variable%"] = $this->replaceVariables($value);
            }
        }

        return $this->dockerVariables;
    }

    protected function replaceVariables($string)
    {
        // Builder Variables
        $args = $this->getDockerVariables();

        // Replace variables into string
        foreach ($args as $arg => $value) {
            $string = str_replace($arg, $value, $string);
        }

        return $string;
    }
}
