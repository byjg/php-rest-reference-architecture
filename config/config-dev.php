<?php

use Builder\Psr11;
use ByJG\AnyDataset\Factory;
use ByJG\RestServer\HandleOutput\JsonHandler;
use ByJG\RestServer\RoutePattern;

return [

    'HOST' => 'localhost',

    'ROUTE_CLASSMAP' => [
        'login' => 'RestTemplate.Rest.Login',
        'sample' => 'RestTemplate.Rest.Sample',
        'sampleprotected' => 'RestTemplate.Rest.SampleProtected',
    ],
    'ROUTE_PATH' => [
        new RoutePattern('POST', '/{module:login}', JsonHandler::class),
    ],
    'ROUTE_PATH_EXTRA' => [
        // Specific for this Environment
        new RoutePattern('GET', '/{module:sample}/{action:ping}', JsonHandler::class),
        new RoutePattern('GET', '/{module:sample}/{action:dummy}/{field}', JsonHandler::class),
        new RoutePattern('POST', '/{module:sample}/{action:dummy}', JsonHandler::class),
        new RoutePattern('GET', '/{module:sampleprotected}/{action:ping}', JsonHandler::class),
    ],

    'JWT_SERVER' => "localhost",
    'JWT_SECRET' => '/R2/isXLfFD+xqxP9rfD/UDVwA5rVZzEe9tQhBYLJrU=',


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
        return new ByJG\Authenticate\UsersDBDataset(
            Psr11::container()->get('DBDRIVER_CONNECTION'),
            new \RestTemplate\Model\UserDefinition(),
            new \ByJG\Authenticate\Definition\UserPropertiesDefinition()
        );
    },



    'BUILDER_VARIABLES' => [
        'buildnum' => "release" . date('YmdHis'),
        'image' => 'resttemplate-%env%',
        'container' => '%image%-instance'
    ],
    'BUILDER_DOCKERFILE' => [
        '# If there any command here, a Dockerfile will be generated with this commands',
        '# If you do not have a custom command, put a single comment like this'
    ],
    'BUILDER_BEFORE_BUILD' => [
        "docker stop %container%"
    ],
    'BUILDER_BUILD' => [
        'docker build -t %image% . ',
    ],
    'BUILDER_DEPLOY_COMMAND' => [
        'docker run -d --rm --name %container% '
        . '-v %workdir%:/srv/web '
        . '-w /srv/web '
        . '--link mysql-container '
        . '-p "80:80" %image%',
    ],
];
