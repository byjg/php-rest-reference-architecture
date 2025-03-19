# Psr11 Container

The project uses the [PSR11](https://www.php-fig.org/psr/psr-11/) container to manage the dependencies.

The configuration per environment is defined in the `config/config-<env>.php` file or `config/config-<env>.env` file or a configuration for all environment in the `config/.env` file.

We are required to set the environment variable `APP_ENV` to the environment name.

Examples:

**config/config-dev.env**

```env
WEB_SERVER=localhost
DASH_SERVER=localhost
WEB_SCHEMA=http
API_SERVER=localhost
API_SCHEMA=http
DBDRIVER_CONNECTION=mysql://root:mysqlp455w0rd@mysql-container/restserver_dev
```

**config/config-dev.env**

```php
<?php
return [
    'WEB_SERVER' => 'localhost',
    'DASH_SERVER' => 'localhost',
    'WEB_SCHEMA' => 'http',
    'API_SERVER' => 'localhost',
    'API_SCHEMA' => 'http',
    'DBDRIVER_CONNECTION' => 'mysql://root:mysqlp455w0rd@mysql-container/restserver_dev'
];
```

The configuration is loaded by the [byjg/config](https://github.com/byjg/config) library.

## Get the configuration

You just need to:

```php
Psr11::get('WEB_SERVER');
```

## Defining the available environments

The available environments are defined in the class `RestReferenceArchitecture\Psr11` in the method `environment()`.

The project has 4 environments:

```text
- dev
-  |- test
-  |- staging
-       |- prod
```

It means that the environment `dev` is the parent of `test` and `staging` and `staging` is the parent of `prod`. A configuration of the bottom environment will override the configuration of the parent environment.

You can change the environments in the `RestReferenceArchitecture\Psr11` class as your needs.

```php
    public static function environment()
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
```
