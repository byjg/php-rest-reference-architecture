# Getting Started - Creating a Table

After [create the project](getting_started.md) you can start to create your own tables. 

## Create the table

You need to create a new file in the `migrations` folder. The file name must be in the format `0000X.sql` where `X` is a number. 
The number is used to order the execution of the scripts.

Create a file `db/migrations/up/00002.sql` with the following content:

```sql
create table example_crud
(
    id int auto_increment not null primary key,
    name varchar(50) not null,
    birthdate datetime null,
    code int null
);
```

To have consistency, we need to create the down script. The down script is used to rollback the changes. 
Create a file `db/migrations/down/00001.sql` with the following content:

```sql
drop table example_crud;
```

## Run the migration

```bash
APP_ENV=dev composer run migrate -- update
```

The result should be:

```text
> Builder\Scripts::migrate
> Command: update
Doing migrate, 2
```

If you want to rollback the changes:

```bash
APP_ENV=dev composer run migrate -- update --up-to=1
```

The result should be:

```text
> Builder\Scripts::migrate
> Command: update
Doing migrate, 1
```

## Generate the CRUD

```bash
APP_ENV=dev composer run migrate -- update                              # Make sure DB is update
APP_ENV=dev composer run codegen -- --table example_crud --save all     # (can be rest, model, test, repo, config)
```

This will create the following files:

- ./src/Rest/ExampleCrudRest.php
- ./src/Model/ExampleCrud.php
- ./src/Repository/ExampleCrudRepository.php
- ./tests/Functional/Rest/ExampleCrudTest.php

To finalize the setup we need to generate the config. 
Run the command bellow copy it contents and save it into the file `config/config-dev.php`

```bash
APP_ENV=dev composer run codegen -- --table example_crud config
```

## First test

The CodeGen is able to create the Unit Test for you. 

It is available in the file `tests/Functional/Rest/ExampleCrudTest.php`.

And you can run by invoking the command:

```bash
composer run test
```

This first test will fail because we don't have the endpoint yet.

```text
ERRORS!
Tests: 36, Assertions: 104, Errors: 2, Failures: 6.
Script ./vendor/bin/phpunit handling the test event returned with error code 2
```

Let's create them now.

## Generate the endpoints from the OpenAPI Documentation

The OpenAPI documentation is generated automatically based on the code.
It is an important step because the documentation is used to create the endpoints and map them to the code. 

If we don't generate the OpenAPI documentation, the new endpoints will not be available.

```bash 
composer run openapi
```

## Fixing the unit test

Now, the endpoint errors passed, but the unit test still failing.

```bash
composer run test
```

```text
PDOException: SQLSTATE[22007]: Invalid datetime format: 1292 Incorrect datetime value: 'birthdate' for column 'birthdate' at row 1

ERRORS!
Tests: 36, Assertions: 111, Errors: 1.
Script ./vendor/bin/phpunit handling the test event returned with error code 2
```

That's because the data used to test is not correct.

Let's open the file `tests/Functional/Rest/ExampleCrudTest.php` and change the data to:

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

Let's change the line:

```text
            'birthdate' => 'birthdate',
```

to

```text
            'birthdate' => '2023-01-01 00:00:00',
```

and run the unit test again:

```bash
composer run test
```

And voila! The test passed!

## Continue the Tutorial

You can continue this tutorial by following the next step: [Add a new field](getting_started_02_add_new_field.md)
