<?php

namespace Builder;

use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\EnvironmentException;
use ByJG\Config\Exception\KeyNotFoundException;
use Closure;
use Psr\SimpleCache\InvalidArgumentException;

class _Lib
{
    protected $container;
    protected $image;
    protected $workdir;
    protected $systemOs;

    public function __construct()
    {
        $this->workdir = realpath(__DIR__ . '/..');
    }

    public function getSystemOs()
    {
        if (!$this->systemOs) {
            $this->systemOs = php_uname('s');
            if (preg_match('/[Dd]arwin/', $this->systemOs)) {
                $this->systemOs = 'Darwin';
            } elseif (preg_match('/[Ww]in/', $this->systemOs)) {
                $this->systemOs = 'Windows';
            }
        }

        return $this->systemOs;
    }

    public function fixDir($command)
    {
        if ($this->getSystemOs() === "Windows") {
            return str_replace('/', '\\', $command);
        }
        return $command;
    }

    /**
     * Execute the given command by displaying console output live to the user.
     *
     * @param  string|array $cmd :  command to be executed
     * @return array   exit_status  :  exit status of the executed command
     *                  output       :  console output of the executed command
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws KeyNotFoundException
     * @throws InvalidArgumentException
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
        if ($this->getSystemOs() === "Windows") {
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

    /**
     * @param string|Closure $variableValue
     * @return mixed
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws KeyNotFoundException
     * @throws InvalidArgumentException
     */
    protected function replaceVariables($variableValue)
    {
        $args = [];
        if (preg_match_all("/%[\\w\\d]+%/", $variableValue, $args)) {
            foreach ($args[0] as $arg) {
                $variableValue = str_replace(
                    $arg,
                    Psr11::container()->get(substr($arg,1, -1)),
                    $variableValue
                );
            }
        };

        return $variableValue;
    }
}
