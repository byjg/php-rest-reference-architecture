<?php


namespace Test\Functional\Rest;

use ByJG\DbMigration\Database\MySqlDatabase;
use ByJG\DbMigration\Migration;
use ByJG\Util\Psr7\Request;
use ByJG\Util\Uri;
use RestTemplate\Psr11;

class BaseApiTestCase extends \ByJG\ApiTools\ApiTestCase
{
    protected static $databaseReset = false;

    public function getPsr7Request()
    {
        $uri = Uri::getInstanceFromString()
            ->withScheme(Psr11::container()->get("API_SCHEMA"))
            ->withHost(Psr11::container()->get("API_SERVER"));

        return Request::getInstance($uri);
    }

    public function resetDb()
    {
        if (!self::$databaseReset) {
            if (Psr11::environment()->getCurrentConfig() != "test") {
                throw new \Exception("This test can only be executed in test environment");
            }
            $migration = new Migration(new Uri(Psr11::container()->get('DBDRIVER_CONNECTION')), __DIR__ . "/../../../db");
            $migration->registerDatabase("mysql", MySqlDatabase::class);
            $migration->prepareEnvironment();
            $migration->reset();
            self::$databaseReset = true;
        }
    }
}
