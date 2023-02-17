<?php

use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\AnyDataset\Db\Factory;
use ByJG\ApiTools\Base\Schema;
use ByJG\Authenticate\Definition\UserDefinition;
use ByJG\Authenticate\Definition\UserPropertiesDefinition;
use ByJG\Authenticate\UsersDBDataset;
use ByJG\Cache\Psr16\BaseCacheEngine;
use ByJG\Cache\Psr16\NoCacheEngine;
use ByJG\Config\DependencyInjection as DI;
use ByJG\Config\Param;
use ByJG\Mail\Envelope;
use ByJG\Mail\MailerFactory;
use ByJG\Mail\Wrapper\MailgunApiWrapper;
use ByJG\Mail\Wrapper\MailWrapperInterface;
use ByJG\MicroOrm\Literal;
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

    UserRepository::class => DI::bind(UserRepository::class)
        ->withInjectedConstructor()
        ->toSingleton(),

    UserDefinition::class => DI::bind(UserDefinition::class)
        ->withConstructorArgs(['users', User::class, UserDefinition::LOGIN_IS_EMAIL])
        ->withMethodCall("markPropertyAsReadOnly", ["uuid"])
        ->withMethodCall("defineClosureForSelect", [
            "userid",
            function ($value, $instance) {
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
                    return new Literal("unhex(replace(uuid(),'-',''))");
                }
                return new Literal("X'" . str_replace('-', '', $value) . "'");
            }
        ])
        ->toSingleton(),

    UserPropertiesDefinition::class => DI::bind(UserPropertiesDefinition::class)
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

    UsersDBDataset::class => DI::bind(UsersDBDataset::class)
        ->withInjectedConstructor()
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


    // -------------------------------------------------------------------------------
    // Use the closure below to add UUID to the MySQL keys instead of auto increment
    // -------------------------------------------------------------------------------

    '_CLOSURE_NEWKEY' => function () {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return new Literal("X'" . bin2hex($data) . "'");
    },
    '_CLOSURE_FIELDMAP_ID' => function ($mapper) {
        $mapper->addFieldMap(
            'id',
            'id',
            function ($value, $instance) {
                if (empty($value)) {
                    return null;
                }
                if (!($value instanceof Literal)) {
                    $value = new Literal("X'$value'");
                }
                return $value;
            },
            function ($value, $instance) {
                return str_replace('-', '', $instance->getUuid());
            }
        );
    },

    '_CLOSURE_FIELDMAP_FKID' => function ($mapper, $fk) {
        $mapper->addFieldMap(
            $fk,
            $fk,
            function ($value, $instance) {
                if (empty($value)) {
                    return null;
                }
                if (!($value instanceof Literal)) {
                    $value = new Literal("X'$value'");
                }
                return $value;
            },
            function ($value, $instance) use ($fk) {
                return str_replace('-', '', $instance->{'get' . $fk . 'uuid'}());
            }
        );
    },

];
