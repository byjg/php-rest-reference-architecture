<?php

use ByJG\Config\DependencyInjection as DI;
use RestReferenceArchitecture\Repository\DummyHexRepository;
use RestReferenceArchitecture\Repository\DummyRepository;

return [

    // Repository Bindings
    DummyRepository::class => DI::bind(DummyRepository::class)
        ->withInjectedConstructor()
        ->toSingleton(),

    DummyHexRepository::class => DI::bind(DummyHexRepository::class)
        ->withInjectedConstructor()
        ->toSingleton(),

];
