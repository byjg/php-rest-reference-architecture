<?php

use Builder\Psr11;
use ByJG\Authenticate\Definition\UserDefinition;
use ByJG\Authenticate\Definition\UserPropertiesDefinition;
use ByJG\Cache\Psr16\NoCacheEngine;
use ByJG\MicroOrm\Literal;
use ByJG\MicroOrm\Mapper;
use ByJG\MicroOrm\Repository;
use ByJG\Util\JwtKeySecret;
use ByJG\Util\JwtWrapper;
use RestTemplate\Model\Dummy;
use RestTemplate\Model\User;

return [

    'CACHE_ROUTES' => function () {
        return new NoCacheEngine();
    },

    'WEB_SERVER' => 'localhost',
    'API_SERVER' => "localhost",
    'JWT_SECRET' => function () {
        return new JwtKeySecret('super_secret_key');
    },


    'DBDRIVER_CONNECTION' => 'mysql://root:password@mysql-container/database',

    'DBDRIVER' => function () {
        $connectionManager = new \ByJG\MicroOrm\ConnectionManager();
        return $connectionManager->addConnection(Psr11::container()->get('DBDRIVER_CONNECTION'));
    },

    'DUMMY_TABLE' => function () {
        $dbDriver = Psr11::container()->get('DBDRIVER');

        $mapper = new Mapper(
            Dummy::class,
            'dummy',
            'id'
        );

        return  new Repository($dbDriver, $mapper);
    },

    'LOGIN' => function () {
        $userDefinition = new UserDefinition(
            'users',
            User::class,
            UserDefinition::LOGIN_IS_EMAIL
        );
        $userDefinition->markPropertyAsReadOnly('uuid');
        $userDefinition->defineClosureForSelect('userid', function ($value, $instance) {
            if (!empty($instance->getUuid())) {
                return $instance->getUuid();
            }
            return $value;
        });
        $userDefinition->defineClosureForUpdate('userid', function ($value) {
            if (empty($value)) {
                return new Literal("unhex(replace(uuid(),'-',''))");
            }
            return new Literal('0x' . str_replace('-', '', $value));
        });

        return new ByJG\Authenticate\UsersDBDataset(
            Psr11::container()->get('DBDRIVER_CONNECTION'),
            $userDefinition,
            new UserPropertiesDefinition()
        );
    },

    'JWT_WRAPPER' => function () {
        return new JwtWrapper(Psr11::container()->get('API_SERVER'), Psr11::container()->get('JWT_SECRET'));
    },

];
