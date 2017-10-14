#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/_lib.php';

class Migrate extends _Lib
{
    private $arguments;

    public function __construct($arguments)
    {
        $this->arguments = $arguments;
        parent::__construct();
    }

    public function run()
    {
        $db = \RestTemplate\Psr11::container()->get('DBDRIVER_CONNECTION');

        $params = implode(' ', array_slice($this->arguments, 1));
        if (!empty($params)) {
            $params .= " $db";
        }

        $cmdLine = __DIR__ . "/vendor/bin/migrate -vvv --path=\"%workdir%/db\" $params";

        $this->liveExecuteCommand($cmdLine);
    }
}

$migrate = new Migrate($argv);
$migrate->run();
