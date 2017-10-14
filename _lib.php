<?php


class _Lib
{
    protected $container;
    protected $image;

    public function __construct()
    {
        $this->image = $this->replaceVariables(\RestTemplate\Psr11::container()->getClosure('DOCKER_IMAGE'));
        $this->container = $this->image . "-instance";
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

        // return exit status and intended output
        return array (
            'exit_status'  => intval($matches[0]),
            'output'       => str_replace("Exit status : " . $matches[0], '', $completeOutput)
        );
    }

    protected function replaceVariables($string)
    {
        // Deploy
        $args = [
            '%env%' => \RestTemplate\Psr11::environment()->getCurrentEnv(),
            '%workdir%' => getcwd(),
            '%container%' => $this->container,
            '%image%' => $this->image
        ];

        foreach ($args as $arg=>$value) {
            $string = str_replace($arg, $value, $string);
        }

        return $string;
    }
}
