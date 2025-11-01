<?php

use ByJG\AnyDataset\Db\DatabaseExecutor;
use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\AnyDataset\Db\Factory;
use ByJG\ApiTools\Base\Schema;
use ByJG\Authenticate\Definition\PasswordDefinition;
use ByJG\Authenticate\Definition\UserDefinition;
use ByJG\Authenticate\Definition\UserPropertiesDefinition;
use ByJG\Authenticate\UsersDBDataset;
use ByJG\Cache\Psr16\BaseCacheEngine;
use ByJG\Cache\Psr16\NoCacheEngine;
use ByJG\Config\DependencyInjection as DI;
use ByJG\Config\Param;
use ByJG\JinjaPhp\Loader\FileSystemLoader;
use ByJG\JwtWrapper\JwtHashHmacSecret;
use ByJG\JwtWrapper\JwtKeyInterface;
use ByJG\JwtWrapper\JwtWrapper;
use ByJG\Mail\Envelope;
use ByJG\Mail\MailerFactory;
use ByJG\Mail\Wrapper\FakeSenderWrapper;
use ByJG\Mail\Wrapper\MailgunApiWrapper;
use ByJG\Mail\Wrapper\MailWrapperInterface;
use ByJG\RestServer\HttpRequestHandler;
use ByJG\RestServer\Middleware\CorsMiddleware;
use ByJG\RestServer\Middleware\JwtMiddleware;
use ByJG\RestServer\OutputProcessor\JsonCleanOutputProcessor;
use ByJG\RestServer\Route\OpenApiRouteList;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RestReferenceArchitecture\Model\User;
use RestReferenceArchitecture\Psr11;
use RestReferenceArchitecture\Repository\DummyHexRepository;
use RestReferenceArchitecture\Repository\DummyRepository;
use RestReferenceArchitecture\Repository\UserDefinition as UserDefinitionAlias;

return [

    BaseCacheEngine::class => DI::bind(NoCacheEngine::class)
        ->toSingleton(),

    OpenAPiRouteList::class => DI::bind(OpenAPiRouteList::class)
        ->withConstructorArgs([
            __DIR__ . '/../public/docs/openapi.json'
        ])
        ->withMethodCall("withDefaultProcessor", [JsonCleanOutputProcessor::class])
        ->withMethodCall("withCache", [Param::get(BaseCacheEngine::class)])
        ->toSingleton(),

    Schema::class => DI::bind(Schema::class)
        ->withFactoryMethod('getInstance', [file_get_contents(__DIR__ . '/../public/docs/openapi.json')])
        ->toSingleton(),

    JwtKeyInterface::class => DI::bind(JwtHashHmacSecret::class)
        ->withConstructorArgs(['jwt_super_secret_key'])
        ->toSingleton(),

    JwtWrapper::class => DI::bind(JwtWrapper::class)
        ->withConstructorArgs([Param::get('API_SERVER'), Param::get(JwtKeyInterface::class)])
        ->toSingleton(),

    MailWrapperInterface::class => function () {
        $apiKey = Psr11::get('EMAIL_CONNECTION');
        MailerFactory::registerMailer(MailgunApiWrapper::class);
        MailerFactory::registerMailer(FakeSenderWrapper::class);

        return MailerFactory::create($apiKey);
    },

    DbDriverInterface::class => DI::bind(Factory::class)
        ->withFactoryMethod("getDbRelationalInstance", [Param::get('DBDRIVER_CONNECTION')])
        ->toSingleton(),

    DatabaseExecutor::class => DI::bind(DatabaseExecutor::class)
        ->withInjectedConstructor()
        ->toSingleton(),

    DummyRepository::class => DI::bind(DummyRepository::class)
        ->withInjectedConstructor()
        ->toSingleton(),

    DummyHexRepository::class => DI::bind(DummyHexRepository::class)
        ->withInjectedConstructor()
        ->toSingleton(),

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

    'CORS_SERVER_LIST' => function () {
        return preg_split('/,(?![^{}]*})/', Psr11::get('CORS_SERVERS'));
    },

    JwtMiddleware::class => DI::bind(JwtMiddleware::class)
        ->withConstructorArgs([
            Param::get(JwtWrapper::class)
        ])
        ->toSingleton(),

    CorsMiddleware::class => DI::bind(CorsMiddleware::class)
        ->withNoConstructor()
        ->withMethodCall("withCorsOrigins", [Param::get("CORS_SERVER_LIST")])  // Required to enable CORS
        // ->withMethodCall("withAcceptCorsMethods", [[/* list of methods */]])     // Optional. Default all methods. Don't need to pass 'OPTIONS'
        // ->withMethodCall("withAcceptCorsHeaders", [[/* list of headers */]])     // Optional. Default all headers
        ->toSingleton(),

    LoggerInterface::class => DI::bind(NullLogger::class)
        ->toSingleton(),

    HttpRequestHandler::class => DI::bind(HttpRequestHandler::class)
        ->withConstructorArgs([
            Param::get(LoggerInterface::class)
        ])
        ->withMethodCall("withMiddleware", [Param::get(JwtMiddleware::class)])
        ->withMethodCall("withMiddleware", [Param::get(CorsMiddleware::class)])
//        ->withMethodCall("withDetailedErrorHandler", [])
        ->toSingleton(),

    // ----------------------------------------------------------------------------

    'MAIL_ENVELOPE' => function ($to, $subject, $template, $mapVariables = []) {
        $body = "";

        $loader = new FileSystemLoader(__DIR__ . "/../templates/emails", ".html");
        $template = $loader->getTemplate($template);
        $body = $template->render($mapVariables);

        $prefix = "";
        if (Psr11::environment()->getCurrentEnvironment() != "prod") {
            $prefix = "[" . Psr11::environment()->getCurrentEnvironment() . "] ";
        }
        return new Envelope(Psr11::get('EMAIL_TRANSACTIONAL_FROM'), $to, $prefix . $subject, $body, true);
    },

];
