<?php

use RestTemplate\Psr11;
use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\AnyDataset\Db\Factory;
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
use ByJG\RestServer\OutputProcessor\JsonCleanOutputProcessor;
use ByJG\RestServer\Route\OpenApiRouteList;
use ByJG\Util\JwtKeySecret;
use ByJG\Util\JwtWrapper;
use RestTemplate\Model\User;
use RestTemplate\Repository\DummyHexRepository;
use RestTemplate\Repository\DummyRepository;
use RestTemplate\Repository\UserRepository;

return [

    'WEB_SERVER' => 'localhost',
    'DASH_SERVER' => 'localhost',
    'WEB_SCHEMA' => "http",
    'API_SERVER' => "localhost",
    'API_SCHEMA' => "http",
    'DBDRIVER_CONNECTION' => 'mysql://root:mysqlp455w0rd@mysql-container/mydb',

    BaseCacheEngine::class => DI::bind(NoCacheEngine::class)
        ->toSingleton(),

    OpenAPiRouteList::class => DI::bind(OpenAPiRouteList::class)
        ->withConstructorArgs([
            __DIR__ . '/../public/docs/swagger.json'
        ])
        ->withMethodCall("withDefaultProcessor", [JsonCleanOutputProcessor::class])
        ->withMethodCall("withCache", [Param::get(BaseCacheEngine::class)])
        ->toSingleton(),

    JwtKeySecret::class => DI::bind(JwtKeySecret::class)
        ->withConstructorArgs(['jwt_super_secret_key'])
        ->toSingleton(),

    JwtWrapper::class => DI::bind(JwtWrapper::class)
        ->withConstructorArgs([Param::get('API_SERVER'), Param::get(JwtKeySecret::class)])
        ->toSingleton(),

    MailWrapperInterface::class => function () {
        $apiKey = "mailgun://uri";
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
        if (Psr11::environment()->getCurrentEnv() != "prod") {
            $prefix = "[" . Psr11::environment()->getCurrentEnv() . "] ";
        }
        return new Envelope("info@example.org", $to, $prefix . $subject, $body, true);
    },


    // -------------------------------------------------------------------------------
    // Use the closure below to add UUID to the MySQL keys instead of auto increment
    // -------------------------------------------------------------------------------

    '_CLOSURE_NEWKEY' => function () {
        return new Literal("X'" . bin2hex(openssl_random_pseudo_bytes(16)) . "'");
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
