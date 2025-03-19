<?php

use ByJG\Cache\Psr16\BaseCacheEngine;
use ByJG\Cache\Psr16\FileSystemCacheEngine;
use ByJG\Config\DependencyInjection as DI;
use ByJG\JwtWrapper\JwtHashHmacSecret;
use ByJG\JwtWrapper\JwtKeyInterface;

return [

    BaseCacheEngine::class => DI::bind(FileSystemCacheEngine::class)->toSingleton(),

    JwtKeyInterface::class => DI::bind(JwtHashHmacSecret::class)
        ->withConstructorArgs(['jwt_super_secret_key'])
        ->toSingleton(),

];
