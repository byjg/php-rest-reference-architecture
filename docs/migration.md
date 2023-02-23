# Database Migration

## Create a new Database

You can create a fresh new database using the command:

```bash
APP_ENV=dev composer migrate -- reset --yes
```

```tip
Use this command carefully. It will drop all tables and create a new database.
```

## Update the Database

It is possible to update the database using the command:

```bash
APP_ENV=dev composer migrate -- update --up-to=x
```

This command will update the database using the migrations files inside the `migrations` folder. It will apply only the migrations that are not applied yet up to the migration number `x`. If you want to apply all migrations just remove the `--up-to=x` parameter.

## Create a new Migration version

Just create a new file inside the folder `db/migrations/up` with the name `DDDDD.sql`. The DDDDD is the number of the migration. The number must be unique and incremental.

If you want to be able to revert the migration, you must create a file inside the folder `db/migrations/down` with the name `DDDDD.sql`. The DDDDD is the number of the migration to go back. The number must be unique and incremental.

You can get more information about the migration process in the [byjg/migration](https://github.com/byjg/migration)
