<?php

namespace RestTemplate;

use ByJG\Cache\Psr16\FileSystemCacheEngine;
use ByJG\Cache\Psr16\NoCacheEngine;
use ByJG\Config\Container;
use ByJG\Config\Definition;
use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\InvalidDateException;
use Psr\SimpleCache\InvalidArgumentException;

class Psr11
{
    private static $definition = null;
    private static $container = null;

    /**
     * @param null $env
     * @return Container
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws InvalidArgumentException
     * @throws InvalidDateException
     */
    public static function container($env = null)
    {
        if (is_null(self::$container)) {
            self::$container = self::environment()->build($env);
        }

        return self::$container;
    }

    /**
     * @return Definition
     * @throws ConfigException
     * @throws InvalidDateException
     */
    public static function environment()
    {
        if (is_null(self::$definition)) {
            self::$definition = (new Definition())
                ->addConfig('dev')
                ->addConfig('test')
                    ->inheritFrom('dev')
                ->addConfig('staging')
                    ->inheritFrom('dev')
                ->addConfig('prod')
                    ->inheritFrom('staging')
                    ->inheritFrom('dev')
                ->setCache(['dev', 'test'], new NoCacheEngine())
                ->setCache(['prod', 'staging'], new FileSystemCacheEngine())
            ;
        }

        return self::$definition;
    }
}
