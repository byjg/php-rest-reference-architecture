<?php

use ByJG\Config\DependencyInjection as DI;
use RestReferenceArchitecture\Repository\ProjectRepository;
use RestReferenceArchitecture\Repository\TaskRepository;

return [

    // Repository Bindings
    ProjectRepository::class => DI::bind(ProjectRepository::class)
        ->withInjectedConstructor()
        // Eager so the ORM mapper and its parentTable relationships register at
        // bootstrap. This lets joinRelated()/joinWith() discover the project<->task<->note
        // path even on a request that only touches one of them (see NoteController::listByProject).
        ->toEagerSingleton(),

    TaskRepository::class => DI::bind(TaskRepository::class)
        ->withInjectedConstructor()
        // Eager so the ORM mapper and its parentTable relationships register at
        // bootstrap. This lets joinRelated()/joinWith() discover the project<->task<->note
        // path even on a request that only touches one of them (see NoteController::listByProject).
        ->toEagerSingleton(),

];
