---
sidebar_position: 6
---

# Database Migration

## Usage

```bash
APP_ENV=<environment> composer migrate -- <command> [options]
composer migrate -- --env=<environment> <command> [options]
```

If you run `composer migrate` with no parameters or invalid parameters, the command will display usage information and available commands.

### Environment

You can specify the environment in two ways:
- Set the `APP_ENV` environment variable
- Use the `--env=<environment>` parameter (overrides `APP_ENV`)

**Note:** At least one method must be used to specify the environment (dev, test, prod).

### Available Commands

| Command   | Description                               |
|-----------|-------------------------------------------|
| `reset`   | Drop all tables and recreate the database |
| `update`  | Apply pending migrations                  |
| `version` | Show current database version             |
| `install` | Install migration tracking table          |

## Create a New Database

You can create a fresh new database using the command:

```bash
# Using APP_ENV
APP_ENV=dev composer migrate -- reset --yes

# Using --env parameter
composer migrate -- --env=dev reset --yes
```

:::warning
Use this command carefully. It will drop all tables and create a new database.
:::

## Update the Database

You can update the database using the command:

```bash
# Using APP_ENV
APP_ENV=dev composer migrate -- update --up-to=x

# Using --env parameter
composer migrate -- --env=dev update --up-to=x
```

This command updates the database using migration files in the `db/migrations` folder. It applies only unapplied migrations up to migration number `x`. To apply all pending migrations, omit the `--up-to=x` parameter.

Apply all pending migrations:

```bash
APP_ENV=dev composer migrate -- update
```

## Create a New Migration Version

Create a new file in the `db/migrations/up` folder with the format `00XXX-description.sql`, where `XXX` is a sequential number:

**Example:** `db/migrations/up/00003-add-users-table.sql`

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL
);
```

### Rollback Support

To support rollbacks, create a corresponding file in `db/migrations/down`:

**Example:** `db/migrations/down/00002-rollback-users-table.sql`

```sql
DROP TABLE users;
```

:::tip
Migration numbers must be unique and sequential. The "down" migration number corresponds to the version you'll have after the rollback.
:::

For more information about the migration process, refer to [byjg/migration](https://github.com/byjg/migration).

---

**[← Previous: Login Integration with JWT](login.md)** | **[Next: Database ORM →](orm.md)**
