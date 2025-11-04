<?php

use ByJG\Cache\Psr16\FileSystemCacheEngine;
use ByJG\Config\Config;
use ByJG\Config\Definition;
use ByJG\Config\Environment;

require_once __DIR__ . '/vendor/autoload.php';

// Check PHP version requirement
if (PHP_INT_SIZE < 8) {
    throw new Exception("This application requires 64-bit PHP");
}

// Define environments with inheritance and caching using fluent API
$dev = Environment::create('dev');
$test = Environment::create('test')
    ->inheritFrom($dev);
$staging = Environment::create('staging')
    ->inheritFrom($dev)
    ->withCache(new FileSystemCacheEngine());
$prod = Environment::create('prod')
    ->inheritFrom($staging, $dev)
    ->withCache(new FileSystemCacheEngine());

// Create definition with all environments
$definition = (new Definition())
    ->addEnvironment($dev)
    ->addEnvironment($test)
    ->addEnvironment($staging)
    ->addEnvironment($prod)
    ->withOSEnvironment([
        'TAG_VERSION',
        'TAG_COMMIT',
    ]);

// Initialize Psr11 with the definition
// The environment will be determined from APP_ENV or default to 'dev'
Config::initialize($definition);
