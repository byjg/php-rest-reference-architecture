<?php

use \ByJG\AnyDataset\Factory;
use \ByJG\RestServer\RoutePattern;
use \ByJG\RestServer\HandleOutput\JsonHandler;
use \Builder\Psr11;

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


    'DBDRIVER_CONNECTION' => 'sqlite://' . __DIR__ . '/../src/sample.db',

    'DUMMY_TABLE' => function () {
        $dbDriver = Factory::getDbRelationalInstance(Psr11::container()->get('DBDRIVER_CONNECTION'));

        $mapper = new \ByJG\MicroOrm\Mapper(
            \RestTemplate\Model\Dummy::class,
            'dummy',
            'id'
        );

        return  new \ByJG\MicroOrm\Repository($dbDriver, $mapper);
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
        'docker run -d --rm --name %container% -v "%workdir%:/srv/web" -p "80:80" %image%',
    ],
];
