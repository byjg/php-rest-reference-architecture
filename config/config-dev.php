<?php

use \ByJG\RestServer\RoutePattern;
use \ByJG\RestServer\HandleOutput\JsonHandler;

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
        new RoutePattern('GET', '/{module:sampleprotected}/{action:ping}', JsonHandler::class),
    ],

    'JWT_SERVER' => "localhost",
    'JWT_SECRET' => '/R2/isXLfFD+xqxP9rfD/UDVwA5rVZzEe9tQhBYLJrU=',


    'DBDRIVER_CONNECTION' => 'sqlite:///tmp/sample.db',

    'TEST_TABLE' => function () {
        $dbDriver = \ByJG\AnyDataset\Factory::getDbRelationalInstance(\RestTemplate\Psr11::container()->get('DBDRIVER_CONNECTION'));

        $mapper = new \ByJG\MicroOrm\Mapper(
            \RestTemplate\Model\Test::class,
            'test',
            'id'
        );

        return  new \ByJG\MicroOrm\Repository($dbDriver, $mapper);
    },




    'DOCKERFILE' => [
        // Specific for this Environment
    ],
    'DOCKER_IMAGE' => function () {
        return 'resttemplate-%env%';
    },
    'DOCKER_BEFORE_BUILD' => [

    ],
    'DOCKER_DEPLOY_COMMAND' => [
        'docker run -d --rm --name %container% -v %workdir%:/srv/web -p "80:80" %image%',
    ],
];
