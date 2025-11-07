<?php


namespace Test\Rest;

use ByJG\ApiTools\Base\Schema;
use ByJG\ApiTools\OpenApiValidation;
use ByJG\Config\Config;
use ByJG\DbMigration\Database\MySqlDatabase;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;
use ByJG\WebRequest\Psr7\Request;
use Exception;
use PHPUnit\Framework\TestCase;

class BaseApiTestCase extends TestCase
{
    use OpenApiValidation;

    protected static bool $databaseReset = false;

    protected string $filePath = __DIR__ . '/../../public/docs/openapi.json';

    protected function setUp(): void
    {
        $this->setSchema(Schema::getInstance(file_get_contents($this->filePath)));
        $this->resetDb();
    }

    protected function tearDown(): void
    {
        $this->setSchema(null);
    }

    public function getPsr7Request(): Request
    {
        $uri = Uri::getInstanceFromString()
            ->withScheme(Config::get("API_SCHEMA"))
            ->withHost(Config::get("API_SERVER"));

        return Request::getInstance($uri);
    }

    public function resetDb()
    {
        if (!self::$databaseReset) {
            if (Config::definition()->getCurrentEnvironment() != "test") {
                throw new Exception("This test can only be executed in test environment");
            }
            Migration::registerDatabase(MySqlDatabase::class);
            $migration = new Migration(new Uri(Config::get('DBDRIVER_CONNECTION')), __DIR__ . "/../../db");
            $migration->prepareEnvironment();
            $migration->reset();
            self::$databaseReset = true;
        }
    }
}
