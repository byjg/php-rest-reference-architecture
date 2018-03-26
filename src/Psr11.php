<?php

namespace RestTemplate;

use ByJG\Config\Definition;

class Psr11
{
    private static $definition = null;
    private static $container = null;

    /**
     * @return \ByJG\Config\Container
     * @throws \ByJG\Config\Exception\ConfigNotFoundException
     * @throws \ByJG\Config\Exception\EnvironmentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
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
     * @throws \ByJG\Config\Exception\EnvironmentException
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
                ->addEnvironment('live')
                    ->inheritFrom('homolog')
                    ->inheritFrom('dev');
            // ->setCache($somePsr16Implementation); // This will cache the result;
        }

        return self::$definition;
    }
}
