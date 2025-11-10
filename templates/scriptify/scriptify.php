<?php

use ByJG\AnyDataset\Db\DatabaseExecutor;
use ByJG\Config\Config;

$executor = Config::get(DatabaseExecutor::class);

function dump($var) {
    var_dump($var);
}

function qq(string $sql, array $params = []) {
    var_dump(Config::get(DatabaseExecutor::class)->getIterator($sql, $params)->toArray());
}
