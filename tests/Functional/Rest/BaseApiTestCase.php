<?php


namespace Test\Functional\Rest;

use ByJG\ApiTools\ApiTestCase;
use ByJG\ApiTools\Base\Schema;
use ByJG\DbMigration\Database\MySqlDatabase;
use ByJG\DbMigration\Migration;
use ByJG\Util\Psr7\Request;
use ByJG\Util\Uri;
use Exception;
use RestReferenceArchitecture\Psr11;

class BaseApiTestCase extends ApiTestCase
{
    protected static bool $databaseReset = false;

    protected string $filePath = __DIR__ . '/../../../public/docs/openapi.json';

    protected function setUp(): void
    {
        $this->setSchema(Schema::getInstance(file_get_contents($this->filePath)));
    }

    protected function tearDown(): void
    {
        $this->setSchema(null);
    }

    public function getPsr7Request(): Request
    {
        $uri = Uri::getInstanceFromString()
            ->withScheme(Psr11::container()->get("API_SCHEMA"))
            ->withHost(Psr11::container()->get("API_SERVER"));

        return Request::getInstance($uri);
    }

    public function resetDb()
    {
        if (!self::$databaseReset) {
            if (Psr11::environment()->getCurrentEnvironment() != "test") {
                throw new Exception("This test can only be executed in test environment");
            }
            Migration::registerDatabase(MySqlDatabase::class);
            $migration = new Migration(new Uri(Psr11::container()->get('DBDRIVER_CONNECTION')), __DIR__ . "/../../../db");
            $migration->prepareEnvironment();
            $migration->reset();
            self::$databaseReset = true;
        }
    }
}
