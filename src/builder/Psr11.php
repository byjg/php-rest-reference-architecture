<?php

namespace Builder;

use ByJG\Config\Container;
use ByJG\Config\Definition;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\EnvironmentException;
use Psr\SimpleCache\InvalidArgumentException;

class Psr11
{
    private static $definition = null;
    private static $container = null;

    /**
     * @return Container
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws InvalidArgumentException
     */
    public static function container()
    {
        if (is_null(self::$container)) {
            self::$container = self::environment()->build();
        }

        return self::$container;
    }


    /**
     * @return Definition
     * @throws EnvironmentException
     */
    public static function environment()
    {
        if (is_null(self::$definition)) {
            self::$definition = (new Definition())
                ->addEnvironment('dev')
                ->addEnvironment('test')
                    ->inheritFrom('dev')
                ->addEnvironment('homolog')
                    ->inheritFrom('dev')
                ->addEnvironment('prod')
                    ->inheritFrom('homolog')
                    ->inheritFrom('dev');
            // ->setCache($somePsr16Implementation); // This will cache the result;
        }

        return self::$definition;
    }
}
