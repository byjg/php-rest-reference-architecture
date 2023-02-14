<?php

use ByJG\Cache\Psr16\BaseCacheEngine;
use ByJG\Cache\Psr16\FileSystemCacheEngine;
use ByJG\Config\DependencyInjection as DI;
use ByJG\Util\JwtKeySecret;

return [

    BaseCacheEngine::class => DI::bind(FileSystemCacheEngine::class)->toSingleton(),

    JwtKeySecret::class => DI::bind(JwtKeySecret::class)
        ->withConstructorArgs(['jwt_super_secret_key'])
        ->toSingleton(),

];
