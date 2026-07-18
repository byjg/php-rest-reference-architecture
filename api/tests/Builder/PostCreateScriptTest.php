<?php

namespace Test\Builder;

use Builder\PostCreateScript;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Tests the create-project file transformation (applyTemplate) against a
 * copy of the real project tree (repo root, spanning api/ + html/). The
 * side-effect part (composer update, npm install, git init) is in finalize()
 * and is exercised end-to-end by the create-project CI job.
 */
class PostCreateScriptTest extends TestCase
{
    protected string $workdir;

    /** Directories not copied into the sandbox (deps / build output / VCS). */
    protected const SKIP_DIRS = ['vendor', 'node_modules', 'dist', '.git', '.idea'];

    protected function setUp(): void
    {
        // repo root is three levels up from api/tests/Builder/
        $projectRoot = realpath(__DIR__ . '/../../..');
        // Sentinels that must exist for the full-tree tests to be meaningful.
        foreach (['docker-compose.yml', 'docker', '.github', 'api/composer.json'] as $item) {
            if (!file_exists("$projectRoot/$item")) {
                // The app image (build-app-image workflow) ships only the runtime subset
                // of the repo; these tests need the full checkout and run in phpunit.yml
                // and, end-to-end, in the create-project workflow.
                $this->markTestSkipped("Template tree incomplete ($item missing) — requires the full repository checkout.");
            }
        }

        $this->workdir = sys_get_temp_dir() . '/create-project-' . uniqid();
        $this->copyRepo($projectRoot, $this->workdir);
    }

    protected function tearDown(): void
    {
        if (!isset($this->workdir)) {
            return; // setUp skipped before creating the workdir
        }
        shell_exec('rm -rf ' . escapeshellarg($this->workdir));
    }

    protected function copyRepo(string $source, string $dest): void
    {
        mkdir($dest, 0755, true);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $sub = $iterator->getSubPathname();
            $segments = explode(DIRECTORY_SEPARATOR, $sub);
            if (array_intersect($segments, self::SKIP_DIRS)) {
                continue;
            }
            $target = "$dest/$sub";
            if ($item->isDir()) {
                @mkdir($target, 0755, true);
            } else {
                @mkdir(dirname($target), 0755, true);
                copy($item->getPathname(), $target);
            }
        }
    }

    protected function applyTemplate(bool $installExamples, bool $installFrontend = true): void
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
                $installExamples,
                $installFrontend
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

        $login = file_get_contents($this->workdir . '/api/src/Controller/LoginController.php');
        $this->assertStringContainsString('namespace AcmeShop\Controller;', $login);
        $this->assertStringContainsString('use ByJG\Gluo\Attribute\ValidateRequest;', $login);
        $this->assertStringContainsString('use ByJG\Gluo\Controller\BaseLoginController;', $login);

        $user = file_get_contents($this->workdir . '/api/src/Model/User.php');
        $this->assertStringContainsString('namespace AcmeShop\Model;', $user);
        $this->assertStringContainsString('use ByJG\Gluo\Model\BaseUser;', $user);
    }

    public function testComposerJsonRenamed(): void
    {
        $this->applyTemplate(true);

        $root = file_get_contents($this->workdir . '/composer.json');
        $this->assertStringContainsString('"name": "acme/shop"', $root);
        $this->assertStringNotContainsString('"byjg/gluo"', $root);

        $api = file_get_contents($this->workdir . '/api/composer.json');
        $this->assertStringContainsString('"name": "acme/shop-api"', $api);
        $this->assertStringContainsString('"byjg/gluo-core"', $api);
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

        $dev = file_get_contents($this->workdir . '/api/config/dev/credentials.env');
        $this->assertStringContainsString('DBDRIVER_CONNECTION=mysql://root:secret123@db-host/shopdev', $dev);

        $test = file_get_contents($this->workdir . '/api/config/test/credentials.env');
        $this->assertStringContainsString('DBDRIVER_CONNECTION=mysql://root:secret123@db-host/shoptest', $test);

        $compose = file_get_contents($this->workdir . '/docker-compose.yml');
        $this->assertStringContainsString('MYSQL_ROOT_PASSWORD: secret123', $compose);
        $this->assertStringNotContainsString('resttest', $compose);
    }

    public function testJwtSecretsAreRegeneratedAndUniquePerEnvironment(): void
    {
        $original = [];
        foreach (['dev', 'test', 'staging', 'prod'] as $env) {
            preg_match('/JWT_SECRET=(.*)/', (string)file_get_contents($this->workdir . "/api/config/$env/credentials.env"), $m);
            $original[$env] = $m[1] ?? '';
        }

        $this->applyTemplate(true);

        $secrets = [];
        foreach (['dev', 'test', 'staging', 'prod'] as $env) {
            preg_match('/JWT_SECRET=(.*)/', (string)file_get_contents($this->workdir . "/api/config/$env/credentials.env"), $m);
            $this->assertNotEmpty($m[1], "JWT_SECRET missing in $env");
            $this->assertNotSame($original[$env], $m[1], "JWT_SECRET not regenerated in $env");
            $secrets[] = $m[1];
        }
        $this->assertCount(4, array_unique($secrets), 'JWT secrets must be unique per environment');
    }

    public function testEnvSampleCreatedWithLocalhostConnection(): void
    {
        $this->applyTemplate(true);

        $sample = file_get_contents($this->workdir . '/api/config/.env.sample');
        $this->assertStringContainsString('mysql://root:secret123@127.0.0.1/shopdev', $sample);
    }

    public function testTemplateMachineryRemoved(): void
    {
        $templateOnlyFiles = [
            '.github/workflows/phpunit.yml',
            '.github/workflows/create-project.yml',
            'api/builder/PostCreateScript.php',
            'api/tests/Builder/PostCreateScriptTest.php',
        ];
        foreach ($templateOnlyFiles as $file) {
            $this->assertFileExists($this->workdir . '/' . $file);
        }

        $this->applyTemplate(true);

        foreach ($templateOnlyFiles as $file) {
            $this->assertFileDoesNotExist($this->workdir . '/' . $file);
        }

        $composer = file_get_contents($this->workdir . '/composer.json');
        $this->assertStringNotContainsString('post-create-project-cmd', $composer);
        $this->assertNotNull(json_decode($composer), 'composer.json must remain valid JSON');
    }

    public function testExamplesKeptWhenRequested(): void
    {
        $this->applyTemplate(true);

        $this->assertFileExists($this->workdir . '/api/src/Model/Project.php');
        $this->assertFileExists($this->workdir . '/api/src/Model/Task.php');
        $this->assertFileExists($this->workdir . '/api/src/Model/Note.php');
        $this->assertFileExists($this->workdir . '/api/src/Controller/SampleController.php');
        $this->assertStringContainsString(
            'namespace AcmeShop\Repository;',
            (string)file_get_contents($this->workdir . '/api/src/Repository/ProjectRepository.php')
        );
    }

    public function testExamplesRemovedWhenNotRequested(): void
    {
        $this->applyTemplate(false);

        $this->assertFileDoesNotExist($this->workdir . '/api/src/Model/Project.php');
        $this->assertFileDoesNotExist($this->workdir . '/api/src/Model/Task.php');
        $this->assertFileDoesNotExist($this->workdir . '/api/src/Model/Note.php');
        $this->assertFileDoesNotExist($this->workdir . '/api/src/Controller/SampleController.php');
        // the rollback lives under down/, not up/ (regression guard for the old path bug)
        $this->assertFileDoesNotExist($this->workdir . '/api/db/migrations/down/00000-rollback-table-examples.sql');

        $repos = file_get_contents($this->workdir . '/api/config/dev/04-repositories.php');
        $this->assertStringNotContainsString('ProjectRepository', $repos);

        $services = file_get_contents($this->workdir . '/api/config/dev/05-services.php');
        $this->assertStringNotContainsString('ProjectService', $services);

        $index = file_get_contents($this->workdir . '/api/public/index.html');
        $this->assertStringNotContainsString('Start Example', $index);

        // Frontend example screens + their router/nav/dashboard markers are stripped,
        // without commenting out the following statement (regression guard).
        if (is_dir($this->workdir . '/html')) {
            $this->assertFileDoesNotExist($this->workdir . '/html/src/pages/examples/ProjectsList.jsx');

            $app = file_get_contents($this->workdir . '/html/src/App.jsx');
            $this->assertStringNotContainsString('ProjectsList', $app);
            $this->assertStringContainsString('function ProtectedLayout', $app);
            $this->assertStringNotContainsString('// function ProtectedLayout', $app);

            $dashboard = file_get_contents($this->workdir . '/html/src/pages/dashboard/Dashboard.jsx');
            $this->assertStringNotContainsString('/projects', $dashboard);
        }
    }

    public function testFrontendKeptWhenRequested(): void
    {
        if (!is_dir($this->workdir . '/html')) {
            $this->markTestSkipped('Frontend (html/) not present in this checkout.');
        }
        $this->applyTemplate(true, installFrontend: true);

        $this->assertDirectoryExists($this->workdir . '/html');
        $this->assertFileExists($this->workdir . '/docker/Dockerfile-html');
        $compose = file_get_contents($this->workdir . '/docker-compose.yml');
        $this->assertStringContainsString('gluo-html', $compose);
    }

    public function testFrontendRemovedWhenNotRequested(): void
    {
        if (!is_dir($this->workdir . '/html')) {
            $this->markTestSkipped('Frontend (html/) not present in this checkout.');
        }
        $this->applyTemplate(true, installFrontend: false);

        $this->assertDirectoryDoesNotExist($this->workdir . '/html');
        $this->assertFileDoesNotExist($this->workdir . '/docker/Dockerfile-html');
        $compose = file_get_contents($this->workdir . '/docker-compose.yml');
        $this->assertStringNotContainsString('gluo-html', $compose);
        $this->assertStringNotContainsString('Dockerfile-html', $compose);
        // the rest + db services must survive
        $this->assertStringContainsString('rest:', $compose);
        $this->assertStringContainsString('image: mysql:', $compose);
    }
}
