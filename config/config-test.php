<?php

use ByJG\Config\DependencyInjection as DI;
use ByJG\JwtWrapper\JwtHashHmacSecret;
use ByJG\JwtWrapper\JwtKeyInterface;

return [
    JwtKeyInterface::class => DI::bind(JwtHashHmacSecret::class)
        ->withConstructorArgs(['jwt_super_secret_key'])
        ->toSingleton(),
];

