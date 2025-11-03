<?php

namespace Builder;

use ByJG\AnyDataset\Db\Factory;
use ByJG\JwtWrapper\JwtWrapper;
use ByJG\Util\Uri;
use Composer\Script\Event;
use Exception;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PostCreateScript
{
    public function execute($workdir, $namespace, $composerName, $phpVersion, $mysqlConnection, $timezone, $installExamples, $gitUserName, $gitUserEmail): void
    {
        // ------------------------------------------------
        // Defining function to interactively walking through the directories
        $directory = new RecursiveDirectoryIterator($workdir);
        $filter = new RecursiveCallbackFilterIterator($directory, function ($current/*, $key, $iterator*/) {
            // Skip hidden files and directories.
            if ($current->getFilename()[0] === '.') {
                return false;
            }
            if ($current->isDir()) {
                // Only recurse into intended subdirectories.
                return $current->getFilename() !== 'fw';
            }
            // else {
            //     // Only consume files of interest.
            //     return strpos($current->getFilename(), 'wanted_filename') === 0;
            // }
            return true;
        });

        $composerName = strtolower($composerName);
        $composerParts = explode("/", $composerName);
        $phpVersionMSimple = str_replace(".", "", $phpVersion);

        // ------------------------------------------------
        //Replace composer name:
        $contents = file_get_contents($workdir . '/composer.json');
        file_put_contents(
            $workdir . '/composer.json',
            str_replace('byjg/rest-reference-architecture', $composerName, $contents)
        );

        // ------------------------------------------------
        // Replace Docker PHP Version
        $files = [ 'docker/Dockerfile' ];
        foreach ($files as $file) {
            $contents = file_get_contents("$workdir/$file");
            $contents = str_replace('ENV TZ=UTC', "ENV TZ=$timezone", $contents);
            $contents = str_replace('php:8.4-fpm', "php:$phpVersion-fpm", $contents);
            $contents = str_replace('php84', "php$phpVersionMSimple", $contents);
            file_put_contents(
                "$workdir/$file",
                $contents
            );
        }

        // ------------------------------------------------
        // Adjusting config files
        $files = [
            'config/dev/credentials.env',
            'config/test/credentials.env',
            'config/staging/credentials.env',
            'config/prod/credentials.env',
            'docker-compose-dev.yml',
        ];
        $uri = new Uri($mysqlConnection);
        foreach ($files as $file) {
            $contents = file_get_contents("$workdir/$file");

            // Common replacements for all files
            $contents = str_replace('mysql://root:mysqlp455w0rd@mysql-container/mydb', "$mysqlConnection", $contents);
            $contents = str_replace('mysql-container', $uri->getHost(), $contents);
            $contents = str_replace('mysqlp455w0rd', $uri->getPassword(), $contents);
            $contents = str_replace('resttest', $composerParts[1], $contents);

            // JWT_SECRET only for .env files - each gets unique secret
            if (str_ends_with($file, '.env')) {
                $jwtSecret = JwtWrapper::generateSecret(64);
                $contents = preg_replace('/JWT_SECRET=.*/', "JWT_SECRET=$jwtSecret", $contents);
            }

            file_put_contents("$workdir/$file", $contents);
        }

        // ------------------------------------------------
        // Adjusting namespace
        $objects = new RecursiveIteratorIterator($filter);
        foreach ($objects as $name => $object) {
            $contents = file_get_contents($name);
            if (str_contains($contents, 'RestReferenceArchitecture')) {
                echo "$name\n";

                // Replace inside Quotes
                $contents = preg_replace(
                    "/(['\"])RestReferenceArchitecture(.*?['\"])/",
                    '$1' . str_replace('\\', '\\\\\\\\', $namespace) . '$2',
                    $contents
                );

                // Replace reserved name
                $contents = str_replace('RestReferenceArchitecture', $namespace, $contents);

                // Replace reserved name
                $contents = str_replace(
                    'rest-reference-architecture',
                    str_replace('/', '', $composerName),
                    $contents
                );

                // Save it
                file_put_contents(
                    $name,
                    $contents
                );
            }
        }

        // ------------------------------------------------
        // Remove example files if not installing examples
        if (!$installExamples) {
            echo "Removing example files...\n";

            // Example files to remove
            $exampleFiles = [
                // Dummy files
                'src/Model/Dummy.php',
                'src/Repository/DummyRepository.php',
                'src/Service/DummyService.php',
                'src/Rest/DummyRest.php',
                'tests/Rest/DummyTest.php',
                // DummyHex files
                'src/Model/DummyHex.php',
                'src/Repository/DummyHexRepository.php',
                'src/Service/DummyHexService.php',
                'src/Rest/DummyHexRest.php',
                'tests/Rest/DummyHexTest.php',
                // DummyActiveRecord files
                'src/Model/DummyActiveRecord.php',
                'src/Rest/DummyActiveRecordRest.php',
                'tests/Rest/DummyActiveRecordTest.php',
                // Sample files
                'src/Rest/Sample.php',
                'src/Rest/SampleProtected.php',
                'tests/Rest/SampleTest.php',
                'tests/Rest/SampleProtectedTest.php',
            ];

            foreach ($exampleFiles as $file) {
                $fullPath = "$workdir/$file";
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                    echo "  Removed: $file\n";
                }
            }

            // Clean up config files
            $configFile = "$workdir/config/dev/04-repositories.php";
            if (file_exists($configFile)) {
                $contents = "<?php\n\nuse ByJG\Config\DependencyInjection as DI;\n\nreturn [\n\n    // Repository Bindings\n\n];\n";
                file_put_contents($configFile, $contents);
                echo "  Cleaned: config/dev/04-repositories.php\n";
            }

            $configFile = "$workdir/config/dev/05-services.php";
            if (file_exists($configFile)) {
                $contents = "<?php\n\nuse ByJG\Config\DependencyInjection as DI;\n\nreturn [\n\n    // Service Bindings\n\n];\n";
                file_put_contents($configFile, $contents);
                echo "  Cleaned: config/dev/05-services.php\n";
            }

            echo "Example files removed successfully.\n";
        }

        // ------------------------------------------------
        // Configure git and initialize repository
        shell_exec("composer update");

        // Initialize git repository first
        shell_exec("git init");
        shell_exec("git branch -m main");

        // Set git user config locally for this repository
        shell_exec('git config user.name ' . escapeshellarg($gitUserName));
        shell_exec('git config user.email ' . escapeshellarg($gitUserEmail));

        shell_exec("git add .");
        shell_exec("git commit -m 'Initial commit'");
    }

    /**
     * @param Event $event
     * @return void
     * @throws Exception
     */
    public static function run(Event $event)
    {
        $workdir = realpath(__DIR__ . '/..');
        $stdIo = $event->getIO();

        // Check if PHP is installed
        $phpCheck = shell_exec('php --version 2>&1');
        if (empty($phpCheck) || !str_contains($phpCheck, 'PHP')) {
            throw new Exception('PHP is not installed or not available in PATH. Please install PHP before proceeding.');
        }

        // Check if git is installed
        $gitCheck = shell_exec('git --version 2>&1');
        if (empty($gitCheck) || !str_contains($gitCheck, 'git version')) {
            throw new Exception('Git is not installed or not available in PATH. Please install Git before proceeding.');
        }

        // Check if docker is installed (warning only)
        $dockerCheck = shell_exec('docker --version 2>&1');
        $dockerInstalled = !empty($dockerCheck) && str_contains($dockerCheck, 'Docker version');

        if (!$dockerInstalled) {
            $stdIo->write("");
            $stdIo->write("<warning>========================================================</warning>");
            $stdIo->write("<warning> WARNING: Docker is not installed</warning>");
            $stdIo->write("<warning>========================================================</warning>");
            $stdIo->write("<warning>Docker was not found on your system.</warning>");
            $stdIo->write("<warning>You will need Docker to run the containerized environment.</warning>");
            $stdIo->write("<warning></warning>");
            $stdIo->write("<warning>You can:</warning>");
            $stdIo->write("<warning>  - Press Ctrl+C to abort and install Docker first</warning>");
            $stdIo->write("<warning>  - Continue without Docker (you'll need to set it up later)</warning>");
            $stdIo->write("<warning>========================================================</warning>");
            $stdIo->write("");
            $stdIo->ask('Press <ENTER> to continue or Ctrl+C to abort');
            $stdIo->write("");
        }

        // Get current git user configuration
        $gitUserName = trim(shell_exec('git config --global user.name 2>/dev/null') ?? '');
        $gitUserEmail = trim(shell_exec('git config --global user.email 2>/dev/null') ?? '');

        $currentPhpVersion = PHP_MAJOR_VERSION . "." .PHP_MINOR_VERSION;

        $validatePHPVersion = function ($arg) {
            $validPHPVersions = ['8.1', '8.2', '8.3', '8.4'];
            if (in_array($arg, $validPHPVersions)) {
                return $arg;
            }
            throw new Exception('Only the PHP versions ' . implode(', ', $validPHPVersions) . ' are supported');
        };

        $validateNamespace = function ($arg) {
            if (empty($arg) || !preg_match('/^[A-Z][a-zA-Z0-9]*$/', $arg)) {
                throw new Exception('Namespace must be one word in CamelCase');
            }
            return $arg;
        };

        $validateComposer = function ($arg) {
            if (empty($arg) || !preg_match('/^[a-z0-9-]+\/[a-z0-9-]+$/', $arg)) {
                throw new Exception('Invalid Composer name');
            }
            return $arg;
        };

        $validateURI = function ($arg) {
            $uri = new Uri($arg);
            if (empty($uri->getScheme())) {
                throw new Exception('Invalid URI');
            }
            Factory::getRegisteredDrivers($uri->getScheme());
            return $arg;
        };

        $validateTimeZone = function ($arg) {
            if (empty($arg) || !in_array($arg, timezone_identifiers_list())) {
                throw new Exception('Invalid Timezone');
            }
            return $arg;
        };

        $validateYesNo = function ($arg) {
            $arg = strtolower(trim($arg));
            if (!in_array($arg, ['yes', 'no', 'y', 'n'])) {
                throw new Exception('Please answer Yes or No (y/n)');
            }
            return in_array($arg, ['yes', 'y']);
        };

        $validateNonEmpty = function ($arg) {
            if (empty(trim($arg))) {
                throw new Exception('This field cannot be empty');
            }
            return trim($arg);
        };

        $validateEmail = function ($arg) {
            $email = trim($arg);
            if (empty($email)) {
                throw new Exception('Email cannot be empty');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }
            return $email;
        };

        $maxRetries = 5;

        $stdIo->write("========================================================");
        $stdIo->write(" Setup Project");
        $stdIo->write(" Answer the questions below");
        $stdIo->write("========================================================");
        $stdIo->write("");
        $stdIo->write("Project Directory: " . $workdir);

        // Git configuration
        $defaultGitName = !empty($gitUserName) ? $gitUserName : 'Your Name';
        $defaultGitEmail = !empty($gitUserEmail) ? $gitUserEmail : 'your.email@example.com';

        $userName = $stdIo->askAndValidate("Git user name [$defaultGitName]: ", $validateNonEmpty, $maxRetries, $defaultGitName);
        $userEmail = $stdIo->askAndValidate("Git user email [$defaultGitEmail]: ", $validateEmail, $maxRetries, $defaultGitEmail);

        // Show info about git configuration
        if ($userName !== $gitUserName && !empty($gitUserName)) {
            $stdIo->write("<info>Git user.name for this project will be set to: $userName</info>");
        }
        if ($userEmail !== $gitUserEmail && !empty($gitUserEmail)) {
            $stdIo->write("<info>Git user.email for this project will be set to: $userEmail</info>");
        }

        $phpVersion = $stdIo->askAndValidate("PHP Version [$currentPhpVersion]: ", $validatePHPVersion, $maxRetries, $currentPhpVersion);
        $namespace = $stdIo->askAndValidate('Project namespace [MyRest]: ', $validateNamespace, $maxRetries, 'MyRest');
        $composerName = $stdIo->askAndValidate('Composer name [me/myrest]: ', $validateComposer, $maxRetries, 'me/myrest');
        $mysqlConnection = $stdIo->askAndValidate('MySQL connection DEV [mysql://root:mysqlp455w0rd@mysql-container/mydb]: ', $validateURI, $maxRetries, 'mysql://root:mysqlp455w0rd@mysql-container/mydb');
        $timezone = $stdIo->askAndValidate('Timezone [UTC]: ', $validateTimeZone, $maxRetries, 'UTC');
        $installExamples = $stdIo->askAndValidate('Install Examples [Yes]: ', $validateYesNo, $maxRetries, 'Yes');
        $stdIo->ask('Press <ENTER> to continue');

        $script = new PostCreateScript();
        $script->execute($workdir, $namespace, $composerName, $phpVersion, $mysqlConnection, $timezone, $installExamples, $userName, $userEmail);
    }
}
