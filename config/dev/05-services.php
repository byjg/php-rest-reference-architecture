<?php

use ByJG\Config\DependencyInjection as DI;
use RestReferenceArchitecture\Service\DummyHexService;
use RestReferenceArchitecture\Service\DummyService;

return [

    // Service Bindings
    DummyService::class => DI::bind(DummyService::class)
        ->withInjectedConstructor()
        ->toSingleton(),

    DummyHexService::class => DI::bind(DummyHexService::class)
        ->withInjectedConstructor()
        ->toSingleton(),

];
