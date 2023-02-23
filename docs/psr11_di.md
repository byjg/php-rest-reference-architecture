# Dependency Injection

Before continue, refer to the [Psr11](psr11.md) documentation and the [Dependency Injection](https://github.com/byjg/config#dependency-injection) documentation.

The main advantage of the Dependency Injection is to decouple the code from the implementation. For example, if you want to cache objects for `prod` environment, but don't for `dev` environment, you can do it easily by creating different implementations of the same interface.

e.g. `config-dev.php`:

```php
return [
    BaseCacheEngine::class => DI::bind(NoCacheEngine::class)
        ->toSingleton(),
]
```

and  `config-prod.php`:

```php
return [
    BaseCacheEngine::class => DI::bind(FileSystemCacheEngine::class)
        ->toSingleton(),
]
```

To use in your code, you just need to set the environment variable `APP_ENV` to the environment name (`dev` or `prod`) and call:

```php
Psr11::container()->get(BaseCacheEngine::class);
```

The application will return the correct implementation based on the environment.
