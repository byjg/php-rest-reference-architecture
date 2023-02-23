<?php

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
use ByJG\Mail\Envelope;
use ByJG\Mail\MailerFactory;
use ByJG\Mail\Wrapper\FakeSenderWrapper;
use ByJG\Mail\Wrapper\MailgunApiWrapper;
use ByJG\Mail\Wrapper\MailWrapperInterface;
use ByJG\MicroOrm\Literal;
use ByJG\RestServer\HttpRequestHandler;
use ByJG\RestServer\Middleware\CorsMiddleware;
use ByJG\RestServer\OutputProcessor\JsonCleanOutputProcessor;
use ByJG\RestServer\Route\OpenApiRouteList;
use ByJG\Util\JwtKeySecret;
use ByJG\Util\JwtWrapper;
use RestTemplate\Model\User;
use RestTemplate\Psr11;
use RestTemplate\Repository\DummyHexRepository;
use RestTemplate\Repository\DummyRepository;
use RestTemplate\Repository\UserRepository;
use RestTemplate\Util\HexUuidLiteral;

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

    JwtKeySecret::class => DI::bind(JwtKeySecret::class)
        ->withConstructorArgs(['jwt_super_secret_key'])
        ->toSingleton(),

    JwtWrapper::class => DI::bind(JwtWrapper::class)
        ->withConstructorArgs([Param::get('API_SERVER'), Param::get(JwtKeySecret::class)])
        ->toSingleton(),

    MailWrapperInterface::class => function () {
        $apiKey = Psr11::container()->get('EMAIL_CONNECTION');
        MailerFactory::registerMailer('mailgun', MailgunApiWrapper::class);
        MailerFactory::registerMailer('fakesender', FakeSenderWrapper::class);

        return MailerFactory::create($apiKey);
    },

    DbDriverInterface::class => DI::bind(Factory::class)
        ->withFactoryMethod("getDbRelationalInstance", [Param::get('DBDRIVER_CONNECTION')])
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

    UserDefinition::class => DI::bind(UserDefinition::class)
        ->withConstructorArgs(['users', User::class, UserDefinition::LOGIN_IS_EMAIL])
        ->withMethodCall("markPropertyAsReadOnly", ["uuid"])
        ->withMethodCall("defineGenerateKeyClosure", [
            function () {
                return new Literal("X'" . Psr11::container()->get(DbDriverInterface::class)->getScalar("SELECT hex(uuid_to_bin(uuid()))") . "'");
            }
        ])
        ->withMethodCall("defineClosureForSelect", [
            "userid",
            function ($value, $instance) {
                if (!method_exists($instance, 'getUuid')) {
                    return $value;
                }
                if (!empty($instance->getUuid())) {
                    return $instance->getUuid();
                }
                return $value;
            }
        ])
        ->withMethodCall("defineClosureForUpdate", [
            'userid',
            function ($value, $instance) {
                if (empty($value)) {
                    return null;
                }
                if (!($value instanceof Literal)) {
                    $value = new HexUuidLiteral($value);
                }
                return $value;
            }
        ])
        ->toSingleton(),

    UserPropertiesDefinition::class => DI::bind(UserPropertiesDefinition::class)
        ->toSingleton(),

    UsersDBDataset::class => DI::bind(UsersDBDataset::class)
        ->withInjectedConstructor()
        ->toSingleton(),

        'CORS_SERVER_LIST' => function () {
        return preg_split('/,(?![^{}]*})/', Psr11::container()->get('CORS_SERVERS'));
    },

    CorsMiddleware::class => DI::bind(CorsMiddleware::class)
        ->withNoConstructor()
        ->withMethodCall("withCorsOrigins", [Param::get("CORS_SERVER_LIST")])  // Required to enable CORS
        // ->withMethodCall("withAcceptCorsMethods", [[/* list of methods */]])     // Optional. Default all methods. Don't need to pass 'OPTIONS'
        // ->withMethodCall("withAcceptCorsHeaders", [[/* list of headers */]])     // Optional. Default all headers
        ->toSingleton(),

    HttpRequestHandler::class => DI::bind(HttpRequestHandler::class)
        ->withMethodCall("withMiddleware", [Param::get(CorsMiddleware::class)])
        ->toSingleton(),

    // ----------------------------------------------------------------------------

    'MAIL_ENVELOPE' => function ($to, $subject, $template, $mapVariables = []) {
        $body = "";

        if (!empty($template)) {
            $body = file_get_contents(__DIR__ . "/../template/$template");
            if (!empty($mapVariables)) {
                foreach ($mapVariables as $key => $value) {
                    $body = str_replace("{{ $key }}", $value, $body);
                }
            }
        }
        $prefix = "";
        if (Psr11::environment()->getCurrentConfig() != "prod") {
            $prefix = "[" . Psr11::environment()->getCurrentConfig() . "] ";
        }
        return new Envelope(Psr11::container()->get('EMAIL_TRANSACTIONAL_FROM'), $to, $prefix . $subject, $body, true);
    },

];
