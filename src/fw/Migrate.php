<?php

namespace Framework;

use Composer\Script\Event;

class Migrate extends _Lib
{
    private $arguments;

    public function __construct($arguments)
    {
        $this->arguments = $arguments;
        parent::__construct();
    }

    public static function run(Event $event)
    {
        $migrate = new Migrate($event->getArguments());
        $migrate->execute();
    }

    public function execute()
    {
        $db = Psr11::container()->get('DBDRIVER_CONNECTION');

        $params = implode(' ', $this->arguments);
        if (!empty($params)) {
            $params .= " $db";
        }

        $cmdLine = $this->workdir . "/vendor/bin/migrate -vvv --path=\"%workdir%/db\" $params";

        $this->liveExecuteCommand($cmdLine);
    }
}

