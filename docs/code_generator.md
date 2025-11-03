---
sidebar_position: 8
---

# Code Generator

The code generator creates PHP classes based on your database tables, dramatically speeding up development.

## What It Generates

The code generator supports two architectural patterns:

### Repository Pattern (Default)
- **Model** - PHP class with properties matching table columns
- **Repository** - Data access layer with ORM integration
- **Service** - Business logic layer extending BaseService
- **REST API** - Complete CRUD endpoints (GET, POST, PUT)
- **Functional Tests** - Test suite for the CRUD API
- **Config** - Automatic DI bindings (added automatically with `--save`)

### ActiveRecord Pattern (with `--activerecord`)
- **Model** - PHP class with properties and ActiveRecord trait for direct database operations
- **REST API** - Complete CRUD endpoints (GET, POST, PUT)
- **Functional Tests** - Test suite for the CRUD API

## Usage

```bash
APP_ENV=<environment> composer codegen -- --table=<table_name> <arguments> [options]
composer codegen -- --env=<environment> --table=<table_name> <arguments> [options]
```

### Required

- `--table=<name>` - Database table name

### Environment

You can specify the environment in two ways:
- Set the `APP_ENV` environment variable
- Use the `--env=<environment>` parameter (overrides `APP_ENV`)

**Note:** At least one method must be used to specify the environment (dev, test, prod).

### Arguments (at least one required)

| Argument               | Description              | Repository Pattern | ActiveRecord Pattern        |
|------------------------|--------------------------|--------------------|-----------------------------|
| `all`                  | Generate all components  | ✓ All components   | ✓ Model, Rest, Test         |
| `model`                | Generate Model           | ✓                  | ✓ (with ActiveRecord trait) |
| `repo` or `repository` | Generate Repository      | ✓                  | ✗ Not applicable            |
| `service`              | Generate Service         | ✓                  | ✗ Not applicable            |
| `rest`                 | Generate REST controller | ✓                  | ✓                           |
| `test`                 | Generate Test            | ✓                  | ✓                           |

### Options

- `--activerecord` - Use ActiveRecord pattern instead of Repository pattern
- `--save` - Save generated files to disk (otherwise prints to console)
- `--debug` - Show debug information

## Examples

### Repository Pattern (Default)

Generate all components for the 'users' table using the Repository pattern:

```bash
# Using APP_ENV
APP_ENV=dev composer codegen -- --table=users all --save

# Using --env parameter
composer codegen -- --env=dev --table=users all --save
```

This creates:
- `src/Model/Users.php`
- `src/Repository/UsersRepository.php`
- `src/Service/UsersService.php`
- `src/Rest/UsersRest.php`
- `tests/Functional/Rest/UsersTest.php`
- Automatically adds DI bindings to `config/dev/04-repositories.php` and `config/dev/05-services.php`

Generate only specific components:

```bash
APP_ENV=dev composer codegen -- --table=products model rest --save
```

### ActiveRecord Pattern

Generate all components for the 'users' table using the ActiveRecord pattern:

```bash
# Using APP_ENV
APP_ENV=test composer codegen -- --table=users all --activerecord --save

# Using --env parameter
composer codegen -- --env=test --table=users all --activerecord --save
```

This creates:
- `src/Model/Users.php` (with ActiveRecord trait)
- `src/Rest/UsersRest.php`
- `tests/Functional/Rest/UsersTest.php`

Generate only the model:

```bash
APP_ENV=test composer codegen -- --table=products model --activerecord --save
```

### Preview Without Saving

Preview the generated REST controller without saving to disk:

```bash
APP_ENV=dev composer codegen -- --table=orders rest
composer codegen -- --env=dev --table=orders all --activerecord
```

## Automatic Configuration

:::tip Automatic DI Bindings (Repository Pattern Only)
When using `--save` with the **Repository pattern**, repository and service bindings are automatically added to the configuration files:
- Repositories → `config/dev/04-repositories.php`
- Services → `config/dev/05-services.php`

No manual configuration needed!

**Note:** ActiveRecord pattern does not require DI bindings since models use the ActiveRecord trait for direct database access.
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

Repository Pattern:
- `model.php.jinja` - Model class template
- `repository.php.jinja` - Repository class template
- `service.php.jinja` - Service class template
- `rest.php.jinja` - REST controller template
- `test.php.jinja` - Test class template

ActiveRecord Pattern:
- `modelactiverecord.php.jinja` - Model class with ActiveRecord trait template
- `restactiverecord.php.jinja` - REST controller for ActiveRecord template
- Uses the same `test.php.jinja` template as Repository pattern

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
