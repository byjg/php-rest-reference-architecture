<?php

use ByJG\Config\DependencyInjection as DI;
use ByJG\Util\JwtKeySecret;

return [
    JwtKeySecret::class => DI::bind(JwtKeySecret::class)
        ->withConstructorArgs(['jwt_super_secret_key'])
        ->toSingleton(),
];
