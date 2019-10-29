<?php

use \ByJG\AnyDataset\Db\Factory;
use \Builder\Psr11;

return [

    'CACHE_ROUTES' => function () {
        return new \ByJG\Cache\Psr16\NoCacheEngine();
    },

    'WEB_SERVER' => 'localhost',
    'API_SERVER' => "localhost",
    'JWT_SECRET' => function () {
        return new \ByJG\Util\JwtKeySecret('/R2/isXLfFD+xqxP9rfD/UDVwA5rVZzEe9tQhBYLJrU=');
    },


    'DBDRIVER_CONNECTION' => 'mysql://root:password@mysql-container/database',

    'DUMMY_TABLE' => function () {
        $dbDriver = Factory::getDbRelationalInstance(Psr11::container()->get('DBDRIVER_CONNECTION'));

        $mapper = new \ByJG\MicroOrm\Mapper(
            \RestTemplate\Model\Dummy::class,
            'dummy',
            'id'
        );

        return  new \ByJG\MicroOrm\Repository($dbDriver, $mapper);
    },

    'LOGIN' => function () {
        $userDefinition = new \ByJG\Authenticate\Definition\UserDefinition(
            'users',
            \RestTemplate\Model\User::class,
            \ByJG\Authenticate\Definition\UserDefinition::LOGIN_IS_EMAIL
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
                return new \ByJG\MicroOrm\Literal("unhex(replace(uuid(),'-',''))");
            }
            return new \ByJG\MicroOrm\Literal('0x' . str_replace('-', '', $value));
        });

        return new ByJG\Authenticate\UsersDBDataset(
            Psr11::container()->get('DBDRIVER_CONNECTION'),
            $userDefinition,
            new \ByJG\Authenticate\Definition\UserPropertiesDefinition()
        );
    },

    'JWT_WRAPPER' => function () {
        return new \ByJG\Util\JwtWrapper(Psr11::container()->get('API_SERVER'), Psr11::container()->get('JWT_SECRET'));
    },



    'BUILDER_VARIABLES' => [
        'project' => 'resttemplate',
        'buildnum' => "release" . date('YmdHis'),
        'image' => function ($variables) {
            return '%project%-%env%' . ($variables['%env%'] !== "dev" ? ':%buildnum%' : '');
        },
        'container' => '%project%-%env%-instance'
    ],

    'BUILDER_DOCKERFILE' => 'docker/Dockerfile-dev',

    'BUILDER_DOCKER_BUILD' => [
        'docker build -t %image% . -f docker/Dockerfile-dev',
    ],

    'BUILDER_DOCKER_RUN' => [
        'docker run -d --rm --name %container% '
        . '-v "%workdir%:/srv/web" '
        . '-w /srv/web '
        . '-e APPLICATION_ENV=%env% '
        . '--link mysql-container '
        . '-p "80:80" %image%',
    ],
];
