<?php

use Builder\Psr11;
use ByJG\AnyDataset\Db\Factory;
use ByJG\Authenticate\Definition\UserDefinition;
use ByJG\Authenticate\Definition\UserPropertiesDefinition;
use ByJG\Cache\Psr16\NoCacheEngine;
use ByJG\MicroOrm\Literal;
use ByJG\MicroOrm\Mapper;
use ByJG\MicroOrm\Repository;
use ByJG\Util\JwtKeySecret;
use ByJG\Util\JwtWrapper;
use RestTemplate\Model\Dummy;
use RestTemplate\Model\DummyHex;
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

    'DUMMY_TABLE' => function () {
        $dbDriver = Factory::getDbRelationalInstance(Psr11::container()->get('DBDRIVER_CONNECTION'));

        $mapper = new Mapper(
            Dummy::class,
            'dummy',
            'id'
        );

        return new Repository($dbDriver, $mapper);
    },

    'DUMMYHEX_TABLE' => function () {
        $dbDriver = Factory::getDbRelationalInstance(Psr11::container()->get('DBDRIVER_CONNECTION'));

        $mapper = new Mapper(
            DummyHex::class,
            'dummyhex',
            'id',
            Psr11::container()->raw("_CLOSURE_NEWKEY")
        );

        Psr11::container()->get('_CLOSURE_FIELDMAP_ID', $mapper);
        $mapper->addFieldMap('uuid', 'uuid', Mapper::doNotUpdateClosure());

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
            return new Literal("X'" . str_replace('-', '', $value) . "'");
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
