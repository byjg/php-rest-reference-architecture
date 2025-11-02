<?php

use ByJG\Authenticate\Definition\PasswordDefinition;
use ByJG\Authenticate\Definition\UserDefinition;
use ByJG\Authenticate\Definition\UserPropertiesDefinition;
use ByJG\Authenticate\UsersDBDataset;
use ByJG\Config\DependencyInjection as DI;
use ByJG\Config\Param;
use ByJG\JwtWrapper\JwtHashHmacSecret;
use ByJG\JwtWrapper\JwtKeyInterface;
use ByJG\JwtWrapper\JwtWrapper;
use RestReferenceArchitecture\Model\User;
use RestReferenceArchitecture\Psr11;
use RestReferenceArchitecture\Repository\UserDefinition as UserDefinitionAlias;

return [

    // JWT Configuration
    JwtKeyInterface::class => DI::bind(JwtHashHmacSecret::class)
        ->withConstructorArgs([Param::get('JWT_SECRET')])
        ->toSingleton(),

    JwtWrapper::class => DI::bind(JwtWrapper::class)
        ->withConstructorArgs([Param::get('API_SERVER'), Param::get(JwtKeyInterface::class)])
        ->toSingleton(),

    // Password Policy
    PasswordDefinition::class => DI::bind(PasswordDefinition::class)
        ->withConstructorArgs([[
            PasswordDefinition::MINIMUM_CHARS => 12,
            PasswordDefinition::REQUIRE_UPPERCASE => 1,  // Number of uppercase characters
            PasswordDefinition::REQUIRE_LOWERCASE => 1,  // Number of lowercase characters
            PasswordDefinition::REQUIRE_SYMBOLS => 1,    // Number of symbols
            PasswordDefinition::REQUIRE_NUMBERS => 1,    // Number of numbers
            PasswordDefinition::ALLOW_WHITESPACE => 0,   // Allow whitespace
            PasswordDefinition::ALLOW_SEQUENTIAL => 0,   // Allow sequential characters
            PasswordDefinition::ALLOW_REPEATED => 0      // Allow repeated characters
        ]])
        ->toSingleton(),

    // User Management
    UserDefinition::class => DI::bind(UserDefinitionAlias::class)
        ->withConstructorArgs(
            [
                'users',       // Table name
                User::class,   // User class
                UserDefinition::LOGIN_IS_EMAIL,
                [
                    // Field name in the User class => Field name in the database
                    'userid' => 'userid',
                    'name' => 'name',
                    'email' => 'email',
                    'username' => 'username',
                    'password' => 'password',
                    'created' => 'created',
                    'admin' => 'admin'
                ]
            ]
        )
        ->toSingleton(),

    UserPropertiesDefinition::class => DI::bind(UserPropertiesDefinition::class)
        ->toSingleton(),

    UsersDBDataset::class => DI::bind(UsersDBDataset::class)
        ->withInjectedConstructor()
        ->toSingleton(),

    // CORS Configuration
    'CORS_SERVER_LIST' => function () {
        return preg_split('/,(?![^{}]*})/', Psr11::get('CORS_SERVERS'));
    },

];
