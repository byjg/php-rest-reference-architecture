<?php

use ByJG\Cache\Psr16\FileSystemCacheEngine;
use ByJG\Config\ConfigInitializeInterface;
use ByJG\Config\Definition;
use ByJG\Config\Environment;

// Check PHP version requirement
if (PHP_INT_SIZE < 8) {
    throw new Exception("This application requires 64-bit PHP");
}

return new class implements ConfigInitializeInterface {
    public function loadDefinition(?string $env = null): Definition
    {
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
        return (new Definition())
            ->addEnvironment($dev)
            ->addEnvironment($test)
            ->addEnvironment($staging)
            ->addEnvironment($prod)
            ->withOSEnvironment([
                'TAG_VERSION',
                'TAG_COMMIT',
            ]);
    }
};
