#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/_lib.php';


$db = \RestTemplate\Psr11::container()->get('DBDRIVER_CONNECTION');

$params = implode(' ', array_slice($argv, 1));
if (!empty($params)) {
    $params .= " $db";
}

$cmdLine = __DIR__ . "/vendor/bin/migrate -vvv --path=\"db\" $params";

liveExecuteCommand($cmdLine);
