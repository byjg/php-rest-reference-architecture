<?php

namespace RestReferenceArchitecture;

use ByJG\Cache\Psr16\FileSystemCacheEngine;
use ByJG\Config\Container;
use ByJG\Config\Definition;
use ByJG\Config\Environment;
use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\InvalidDateException;
use Psr\SimpleCache\InvalidArgumentException;

class Psr11
{
    private static ?Definition $definition = null;
    private static ?Container $container = null;

    /**
     * @param string|null $env
     * @return Container
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws InvalidArgumentException
     * @throws InvalidDateException
     */
    public static function container(?string $env = null): ?Container
    {
        if (is_null(self::$container)) {
            if (PHP_INT_SIZE < 8) {
                throw new \Exception("This application requires 64-bit PHP");
            }
            self::$container = self::environment()->build($env);
        }

        return self::$container;
    }

    /**
     * @return Definition|null
     * @throws ConfigException
     */
    public static function environment(): ?Definition
    {
        $dev = new Environment('dev');
        $test = new Environment('test', [$dev]);
        $staging = new Environment('staging', [$dev], new FileSystemCacheEngine());
        $prod = new Environment('prod', [$staging, $dev], new FileSystemCacheEngine());

        if (is_null(self::$definition)) {
            self::$definition = (new Definition())
                ->addEnvironment($dev)
                ->addEnvironment($test)
                ->addEnvironment($staging)
                ->addEnvironment($prod)
            ;
        }

        return self::$definition;
    }
}
