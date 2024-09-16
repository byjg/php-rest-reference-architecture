# Login with JWT

This project comes with a management user, login and JWT out of the box.

For most of the use cases you don't need to change the application. Just the dependency injection configuration.

## Configure the Login

Here the main guidelines:

The database table created by the project is `users` with the following structure:

```sql
CREATE TABLE `users` (
    userid binary(16) DEFAULT (uuid_to_bin(uuid())) NOT NULL,
    `uuid` varchar(36) GENERATED ALWAYS AS (insert(insert(insert(insert(hex(`userid`),9,0,'-'),14,0,'-'),19,0,'-'),24,0,'-')) VIRTUAL,
    name varchar(50),
    email varchar(120),
    username varchar(20) not null,
    password char(40) not null,
    created datetime,
    admin enum('yes','no'),
    PRIMARY KEY (userid)
);
```

and the RestReferenceArchitecture/Model/User.php has the mapping for this table.

If you have the same fields but named differently, you can change the mapping in the `config/config_dev.php` file:

```php
    UserDefinition::class => DI::bind(UserDefinition::class)
        ->withConstructorArgs(
            [
                'users',
                User::class,
                UserDefinition::LOGIN_IS_EMAIL,
                [
                    // Field name in the User class => Field name in the database
                    'userid'   => 'userid',
                    'name'     => 'name',
                    'email'    => 'email',
                    'username' => 'username',
                    'password' => 'password',
                    'created'  => 'created',
                    'admin'    => 'admin'
                ]
            ]
        )
    );
```

You can modify completely this structure by referring the documentation of the project [byjg/authuser](https://github.com/byjg/authuser).

## Configure Password Rule Enforcement

You can configure how the password will be saved by changing here:

```php
    PasswordDefinition::class => DI::bind(PasswordDefinition::class)
        ->withConstructorArgs([[
            PasswordDefinition::MINIMUM_CHARS => 12,
            PasswordDefinition::REQUIRE_UPPERCASE => 1,  // Number of uppercase characters
            PasswordDefinition::REQUIRE_LOWERCASE => 1,  // Number of lowercase characters
            PasswordDefinition::REQUIRE_SYMBOLS => 1,    // Number of symbols
            PasswordDefinition::REQUIRE_NUMBERS => 1,    // Number of numbers
            PasswordDefinition::ALLOW_WHITESPACE => 0,   // Allow whitespace
            PasswordDefinition::ALLOW_SEQUENTIAL => 0,   // Allow sequential characters
            PasswordDefinition::ALLOW_REPEATED => 0      // Allow repeated characters
        ]])
        ->toSingleton(),
```

## Configure the JWT

There is an endpoint to generate the JWT token. The endpoint is `/login` and the method is `POST`.

You can test it using the endpoint `/sampleprotected/ping` with the method `GET` passing the header `Authorization: Bearer <token>`.

Also, there is an endpoint to refresh the token. The endpoint is `/refresh` and the method is `POST`.

To configure the key you can change here:

```php
    JwtKeyInterface::class => DI::bind(\ByJG\JwtWrapper\JwtHashHmacSecret::class)
        ->withConstructorArgs(['supersecretkeyyoushouldnotcommittogithub'])
        ->toSingleton(),
```

More information on the [byjg/jwt-wrapper](https://github.com/byjg/jwt-wrapper)
