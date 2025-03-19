# Getting Started - Creating a Table

After [creating the project](getting_started.md), you're ready to create your own tables.

## Create the Table

Create a new migration file in the `migrations` folder using the format `0000X-message.sql`, where `X` represents a sequential number that determines execution order.

1. Create an "up" migration file `db/migrations/up/00002-create-table-example.sql`:

```sql
create table example_crud
(
    id int auto_increment not null primary key,
    name varchar(50) not null,
    birthdate datetime null,
    code int null
);
```

2. Create a corresponding "down" migration file `db/migrations/down/00001-rollback-table-example.sql` for rollbacks:

```sql
drop table example_crud;
```

## Run the Migration

Apply your migrations with:

```shell
APP_ENV=dev composer run migrate -- update
```

Expected output:
```text
> Builder\Scripts::migrate
> Command: update
Doing migrate, 2
```

To rollback changes:

```shell
APP_ENV=dev composer run migrate -- update --up-to=1
```

The result should be:

```text
> Builder\Scripts::migrate
> Command: update
Doing migrate, 1
```

Remember to run the migrate update again to apply the changes.


## Generate CRUD Components with the Code Generator

Generate all necessary files for your new table:

```shell
# Ensure DB is updated first
APP_ENV=dev composer run migrate -- update

# Generate files (options: rest, model, test, repo, config, or all)
APP_ENV=dev composer run codegen -- --table example_crud --save all
```

This creates:
- `./src/Rest/ExampleCrudRest.php`
- `./src/Model/ExampleCrud.php`
- `./src/Repository/ExampleCrudRepository.php`
- `./tests/Functional/Rest/ExampleCrudTest.php`

You have a manual step to generate the configuration by running the command below and adding it to `config/config-dev.php` 

```shell
APP_ENV=dev composer run codegen -- --table example_crud config
```

## Run the Tests

The automatically generated test is located at `tests/Functional/Rest/ExampleCrudTest.php`.

Run it:

```shell
composer run test
```

Initial tests **_will fail_** because we need to:

1. Generate OpenAPI documentation to create the endpoints:

```shell
composer run openapi
```

2. Fix the test data by updating `tests/Rest/ExampleCrudTest.php`:

 
Locate:

```php
    protected function getSampleData($array = false)
    {
        $sample = [

            'name' => 'name',
            'birthdate' => 'birthdate',
            'code' => 1,
        ];
...
```

And Change:
```php
'birthdate' => 'birthdate',
```

To:

```php
'birthdate' => '2023-01-01 00:00:00',
```

3. Run the tests again:
```shell
composer run test
```

Your tests should now pass successfully!

## Next Steps

Continue with [Adding a New Field](getting_started_02_add_new_field.md) to enhance your implementation.
