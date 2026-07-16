<?php

namespace Test\Builder;

use Builder\PostCreateScript;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Tests the create-project file transformation (applyTemplate) against a
 * copy of the real project tree. The side-effect part (composer update,
 * git init) is in finalize() and is exercised by the create-project CI job.
 */
class PostCreateScriptTest extends TestCase
{
    protected string $workdir;

    protected const COPY_ITEMS = [
        'composer.json',
        'docker-compose.yml',
        'docker',
        'config',
        'public',
        'src',
        'builder',
        'db',
        'templates',
        '.github',
    ];

    protected function setUp(): void
    {
        $this->workdir = sys_get_temp_dir() . '/create-project-' . uniqid();
        mkdir($this->workdir, 0755, true);
        $projectRoot = realpath(__DIR__ . '/../..');
        foreach (self::COPY_ITEMS as $item) {
            $this->copyRecursive("$projectRoot/$item", "$this->workdir/$item");
        }
    }

    protected function tearDown(): void
    {
        shell_exec('rm -rf ' . escapeshellarg($this->workdir));
    }

    protected function copyRecursive(string $source, string $dest): void
    {
        if (is_file($source)) {
            @mkdir(dirname($dest), 0755, true);
            copy($source, $dest);
            return;
        }
        if (!is_dir($source)) {
            return;
        }
        mkdir($dest, 0755, true);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $target = $dest . '/' . $iterator->getSubPathname();
            if ($item->isDir()) {
                mkdir($target, 0755, true);
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }

    protected function applyTemplate(bool $installExamples): void
    {
        $script = new PostCreateScript();
        ob_start();
        try {
            $script->applyTemplate(
                $this->workdir,
                'AcmeShop',
                'acme/shop',
                '8.4',
                [
                    'schema' => 'mysql',
                    'host' => 'db-host',
                    'user' => 'root',
                    'password' => 'secret123',
                    'dev_database' => 'shopdev',
                    'test_database' => 'shoptest',
                ],
                'America/Sao_Paulo',
                $installExamples
            );
        } finally {
            ob_end_clean();
        }
    }

    protected function findFilesContaining(string $needle): array
    {
        $found = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->workdir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $item) {
            if ($item->isFile() && str_contains((string)file_get_contents($item->getPathname()), $needle)) {
                $found[] = substr($item->getPathname(), strlen($this->workdir) + 1);
            }
        }
        return $found;
    }

    public function testNamespaceIsFullyReplacedAndGluoImportsUntouched(): void
    {
        $this->applyTemplate(true);

        $this->assertSame([], $this->findFilesContaining('RestReferenceArchitecture'));

        $login = file_get_contents($this->workdir . '/src/Controller/LoginController.php');
        $this->assertStringContainsString('namespace AcmeShop\Controller;', $login);
        $this->assertStringContainsString('use ByJG\Gluo\Attribute\ValidateRequest;', $login);
        $this->assertStringContainsString('use ByJG\Gluo\Controller\BaseLoginController;', $login);

        $user = file_get_contents($this->workdir . '/src/Model/User.php');
        $this->assertStringContainsString('namespace AcmeShop\Model;', $user);
        $this->assertStringContainsString('use ByJG\Gluo\Model\BaseUser;', $user);
    }

    public function testComposerJsonRenamed(): void
    {
        $this->applyTemplate(true);

        $composer = file_get_contents($this->workdir . '/composer.json');
        $this->assertStringContainsString('"acme/shop"', $composer);
        $this->assertStringNotContainsString('byjg/rest-reference-architecture', $composer);
    }

    public function testDockerfileVersionAndTimezone(): void
    {
        $this->applyTemplate(true);

        $dockerfile = file_get_contents($this->workdir . '/docker/Dockerfile');
        $this->assertStringContainsString('ENV TZ=America/Sao_Paulo', $dockerfile);
        $this->assertStringContainsString('php:8.4-fpm', $dockerfile);
        $this->assertStringNotContainsString('php85', $dockerfile);
    }

    public function testConnectionStringsAndCredentials(): void
    {
        $this->applyTemplate(true);

        $dev = file_get_contents($this->workdir . '/config/dev/credentials.env');
        $this->assertStringContainsString('DBDRIVER_CONNECTION=mysql://root:secret123@db-host/shopdev', $dev);

        $test = file_get_contents($this->workdir . '/config/test/credentials.env');
        $this->assertStringContainsString('DBDRIVER_CONNECTION=mysql://root:secret123@db-host/shoptest', $test);

        $compose = file_get_contents($this->workdir . '/docker-compose.yml');
        $this->assertStringContainsString('MYSQL_ROOT_PASSWORD: secret123', $compose);
        $this->assertStringNotContainsString('resttest', $compose);
    }

    public function testJwtSecretsAreRegeneratedAndUniquePerEnvironment(): void
    {
        $original = [];
        foreach (['dev', 'test', 'staging', 'prod'] as $env) {
            preg_match('/JWT_SECRET=(.*)/', (string)file_get_contents($this->workdir . "/config/$env/credentials.env"), $m);
            $original[$env] = $m[1] ?? '';
        }

        $this->applyTemplate(true);

        $secrets = [];
        foreach (['dev', 'test', 'staging', 'prod'] as $env) {
            preg_match('/JWT_SECRET=(.*)/', (string)file_get_contents($this->workdir . "/config/$env/credentials.env"), $m);
            $this->assertNotEmpty($m[1], "JWT_SECRET missing in $env");
            $this->assertNotSame($original[$env], $m[1], "JWT_SECRET not regenerated in $env");
            $secrets[] = $m[1];
        }
        $this->assertCount(4, array_unique($secrets), 'JWT secrets must be unique per environment');
    }

    public function testEnvSampleCreatedWithLocalhostConnection(): void
    {
        $this->applyTemplate(true);

        $sample = file_get_contents($this->workdir . '/config/.env.sample');
        $this->assertStringContainsString('mysql://root:secret123@127.0.0.1/shopdev', $sample);
    }

    public function testPhpunitWorkflowRemoved(): void
    {
        $this->assertFileExists($this->workdir . '/.github/workflows/phpunit.yml');

        $this->applyTemplate(true);

        $this->assertFileDoesNotExist($this->workdir . '/.github/workflows/phpunit.yml');
    }

    public function testExamplesKeptWhenRequested(): void
    {
        $this->applyTemplate(true);

        $this->assertFileExists($this->workdir . '/src/Model/Dummy.php');
        $this->assertFileExists($this->workdir . '/src/Controller/SampleController.php');
        $this->assertStringContainsString(
            'namespace AcmeShop\Repository;',
            (string)file_get_contents($this->workdir . '/src/Repository/DummyRepository.php')
        );
    }

    public function testExamplesRemovedWhenNotRequested(): void
    {
        $this->applyTemplate(false);

        $this->assertFileDoesNotExist($this->workdir . '/src/Model/Dummy.php');
        $this->assertFileDoesNotExist($this->workdir . '/src/Model/DummyHex.php');
        $this->assertFileDoesNotExist($this->workdir . '/src/Model/DummyActiveRecord.php');
        $this->assertFileDoesNotExist($this->workdir . '/src/Controller/SampleController.php');

        $repos = file_get_contents($this->workdir . '/config/dev/04-repositories.php');
        $this->assertStringNotContainsString('DummyRepository', $repos);

        $services = file_get_contents($this->workdir . '/config/dev/05-services.php');
        $this->assertStringNotContainsString('DummyService', $services);

        $index = file_get_contents($this->workdir . '/public/index.html');
        $this->assertStringNotContainsString('Start Example', $index);
    }
}
