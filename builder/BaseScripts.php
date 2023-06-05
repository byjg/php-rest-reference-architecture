<?php

namespace Builder;

use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\InvalidDateException;
use ByJG\Config\Exception\KeyNotFoundException;
use Closure;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use RestTemplate\Psr11;

class BaseScripts
{
    protected $workdir;
    protected string $systemOs;

    public function __construct()
    {
        $this->workdir = realpath(__DIR__ . '/..');
    }

    public function getSystemOs(): string
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
     * @param string|array $cmd :  command to be executed
     * @return array   exit_status  :  exit status of the executed command
     *                  output       :  console output of the executed command
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws InvalidArgumentException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     */
    protected function liveExecuteCommand($cmd): ?array
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
     * @return array|Closure|string|string[]
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     * @throws ConfigException
     * @throws InvalidDateException
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
        }

        return $variableValue;
    }
}
