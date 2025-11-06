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
        // Remove Documentation job from build-app-image.yml workflow
        $workflowFile = "$workdir/.github/workflows/build-app-image.yml";
        if (file_exists($workflowFile)) {
            $contents = file_get_contents($workflowFile);

            // Remove the Documentation job section (from "  Documentation:" to the end of file)
            $contents = preg_replace(
                '/\n\n  Documentation:\n.*$/s',
                '',
                $contents
            );

            file_put_contents($workflowFile, $contents);
            echo "Removed Documentation job from .github/workflows/build-app-image.yml\n";
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

        // Generate OpenAPI documentation
        shell_exec("composer run openapi");

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
     * Load configuration from JSON file if exists
     * Checks multiple locations in priority order
     *
     * @param string $workdir
     * @return array|null
     */
    protected static function loadConfigFromJson(string $workdir): ?array
    {
        $locations = [
            // 1. Environment variable (highest priority)
            getenv('SETUP_JSON'),

            // 2. Parent directory (where user ran composer create-project)
            dirname($workdir) . '/setup.json',

            // 3. User's home directory
            (getenv('HOME') ?: getenv('USERPROFILE')) . '/.rest-reference-architecture/setup.json',
        ];

        foreach ($locations as $configFile) {
            if (!empty($configFile) && file_exists($configFile)) {
                $json = file_get_contents($configFile);
                $config = json_decode($json, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $config;
                }
            }
        }

        return null;
    }

    /**
     * @param Event $event
     * @return void
     * @throws Exception
     */
    public static function run(Event $event)
    {
        $workdir = getcwd();
        $stdIo = $event->getIO();

        // Check for unattended mode via JSON config
        $config = self::loadConfigFromJson($workdir);
        $unattended = $config !== null;

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
            if (!$unattended) {
                $stdIo->write("<warning>You can:</warning>");
                $stdIo->write("<warning>  - Press Ctrl+C to abort and install Docker first</warning>");
                $stdIo->write("<warning>  - Continue without Docker (you'll need to set it up later)</warning>");
                $stdIo->write("<warning>========================================================</warning>");
                $stdIo->write("");
                $stdIo->ask('Press <ENTER> to continue or Ctrl+C to abort');
                $stdIo->write("");
            } else {
                $stdIo->write("<warning>Continuing in unattended mode...</warning>");
                $stdIo->write("<warning>========================================================</warning>");
                $stdIo->write("");
            }
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

        if ($unattended) {
            // Unattended mode - use config from JSON
            $stdIo->write("========================================================");
            $stdIo->write(" Setup Project - UNATTENDED MODE");
            $stdIo->write("========================================================");
            $stdIo->write("");
            $stdIo->write("Project Directory: " . $workdir);

            // Get values with defaults
            $userName = $config['git_user_name'] ?? $gitUserName ?: 'Your Name';
            $userEmail = $config['git_user_email'] ?? $gitUserEmail ?: 'your.email@example.com';
            $phpVersion = $config['php_version'] ?? $currentPhpVersion;
            $namespace = $config['namespace'] ?? 'MyRest';
            $composerName = $config['composer_name'] ?? 'me/myrest';
            $mysqlConnection = $config['mysql_connection'] ?? 'mysql://root:mysqlp455w0rd@mysql-container/mydb';
            $timezone = $config['timezone'] ?? 'UTC';
            $installExamples = $config['install_examples'] ?? true;

            // Validate provided values (only if they were explicitly provided in JSON)
            if (isset($config['git_user_name'])) {
                try {
                    $userName = $validateNonEmpty($userName);
                } catch (Exception $e) {
                    throw new Exception("Invalid git_user_name in setup.json: " . $e->getMessage());
                }
            }

            if (isset($config['git_user_email'])) {
                try {
                    $userEmail = $validateEmail($userEmail);
                } catch (Exception $e) {
                    throw new Exception("Invalid git_user_email in setup.json: " . $e->getMessage());
                }
            }

            if (isset($config['php_version'])) {
                try {
                    $phpVersion = $validatePHPVersion($phpVersion);
                } catch (Exception $e) {
                    throw new Exception("Invalid php_version in setup.json: " . $e->getMessage());
                }
            }

            if (isset($config['namespace'])) {
                try {
                    $namespace = $validateNamespace($namespace);
                } catch (Exception $e) {
                    throw new Exception("Invalid namespace in setup.json: " . $e->getMessage());
                }
            }

            if (isset($config['composer_name'])) {
                try {
                    $composerName = $validateComposer($composerName);
                } catch (Exception $e) {
                    throw new Exception("Invalid composer_name in setup.json: " . $e->getMessage());
                }
            }

            if (isset($config['mysql_connection'])) {
                try {
                    $mysqlConnection = $validateURI($mysqlConnection);
                } catch (Exception $e) {
                    throw new Exception("Invalid mysql_connection in setup.json: " . $e->getMessage());
                }
            }

            if (isset($config['timezone'])) {
                try {
                    $timezone = $validateTimeZone($timezone);
                } catch (Exception $e) {
                    throw new Exception("Invalid timezone in setup.json: " . $e->getMessage());
                }
            }

            if (isset($config['install_examples']) && !is_bool($config['install_examples'])) {
                throw new Exception("Invalid install_examples in setup.json: must be true or false (boolean)");
            }

            $stdIo->write("Git user name: $userName");
            $stdIo->write("Git user email: $userEmail");
            $stdIo->write("PHP Version: $phpVersion");
            $stdIo->write("Namespace: $namespace");
            $stdIo->write("Composer name: $composerName");
            $stdIo->write("MySQL connection: $mysqlConnection");
            $stdIo->write("Timezone: $timezone");
            $stdIo->write("Install Examples: " . ($installExamples ? 'Yes' : 'No'));
            $stdIo->write("");
        } else {
            // Interactive mode
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
        }

        $script = new PostCreateScript();
        $script->execute($workdir, $namespace, $composerName, $phpVersion, $mysqlConnection, $timezone, $installExamples, $userName, $userEmail);
    }
}
