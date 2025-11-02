---
sidebar_position: 8
---

# Code Generator

The code generator creates PHP classes based on your database tables, dramatically speeding up development.

## What It Generates

The code generator can create:

- **Model** - PHP class with properties matching table columns
- **Repository** - Data access layer with ORM integration
- **Service** - Business logic layer extending BaseService
- **REST API** - Complete CRUD endpoints (GET, POST, PUT)
- **Functional Tests** - Test suite for the CRUD API
- **Config** - Automatic DI bindings (added automatically with `--save`)

## How to Use

```bash
APP_ENV=dev composer run codegen -- --table <table_name> <class_type> [--save] [--debug]
```

### Parameters

- `--table <table_name>` - **(Required)** Database table name
- `<class_type>` - **(Required)** What to generate (see options below)
- `--save` - **(Optional)** Save files to disk (otherwise prints to console)
- `--debug` - **(Optional)** Print table structure for debugging

### Class Types

| Type | Generates | Saved Location |
|------|-----------|----------------|
| `model` | Model class | `src/Model/` |
| `repo` or `repository` | Repository class | `src/Repository/` |
| `service` | Service class | `src/Service/` |
| `rest` | REST controller | `src/Rest/` |
| `test` | Functional tests | `tests/Functional/Rest/` |
| `all` | All of the above | Multiple locations |

## Examples

### Generate All Files

```bash
# Generate everything for the 'users' table
APP_ENV=dev composer run codegen -- --table users all --save
```

This creates:
- `src/Model/Users.php`
- `src/Repository/UsersRepository.php`
- `src/Service/UsersService.php`
- `src/Rest/UsersRest.php`
- `tests/Functional/Rest/UsersTest.php`
- Automatically adds DI bindings to `config/dev/04-repositories.php` and `config/dev/05-services.php`

### Generate Only Model and Repository

```bash
APP_ENV=dev composer run codegen -- --table products model --save
APP_ENV=dev composer run codegen -- --table products repository --save
```

### Preview Without Saving

```bash
# Preview the generated REST controller
APP_ENV=dev composer run codegen -- --table orders rest
```

## Automatic Configuration

:::tip Automatic DI Bindings
When using `--save`, repository and service bindings are automatically added to the configuration files:
- Repositories → `config/dev/04-repositories.php`
- Services → `config/dev/05-services.php`

No manual configuration needed!
:::

Example output:
```
Processing Repository for table users...
File saved in src/Repository/UsersRepository.php
Added use statement for UsersRepository to 04-repositories.php
Added DI binding for UsersRepository to 04-repositories.php
```

## Important Notes

:::warning Overwriting Files
Using `--save` will **overwrite existing files** without warning. Be careful when regenerating files you've customized.
:::

:::info After Generation
After generating REST controllers, remember to:
1. Run `composer run openapi` to update the OpenAPI specification
2. Run `composer run test` to verify the generated tests pass
:::

## Customizing Templates

You can modify existing templates or create your own. Templates are located in `templates/codegen/` and use the [Jinja template engine for PHP](https://github.com/byjg/jinja_php).

**Available templates:**
- `model.php.jinja` - Model class template
- `repository.php.jinja` - Repository class template
- `service.php.jinja` - Service class template
- `rest.php.jinja` - REST controller template
- `test.php.jinja` - Test class template

**Template variables available:**
- `className` - PascalCase class name (e.g., `UserProfile`)
- `tableName` - Original table name (e.g., `user_profile`)
- `namespace` - Project namespace
- `fields` - Array of table columns with types
- `primaryKeys` - Array of primary key fields
- `nullableFields` - Array of nullable fields
- `nonNullableFields` - Array of non-nullable, non-PK fields

---

**[← Previous: Database ORM](orm.md)** | **[Next: Service Layer →](services.md)**
