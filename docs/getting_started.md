---
sidebar_position: 10
---

# Getting Started

## Requirements

- **Docker Engine**: For containerizing your application
- **PHP 8.3+**: For local development
- **IDE**: Any development environment of your choice

> **Windows Users**: If you don't have PHP installed in WSL2, please follow the [Windows](windows.md) guide.

### Required PHP Extensions

- ctype
- curl
- dom
- filter
- hash
- json
- libxml
- mbstring
- openssl
- pcre
- pdo
- pdo_mysql
- phar
- simplexml
- tokenizer
- xml
- xmlwriter

## Installation

Choose one of the following installation methods:

```shell script
# Standard installation
mkdir ~/tutorial
composer create-project byjg/rest-reference-architecture ~/tutorial ^6.0

# OR Latest development version
mkdir ~/tutorial
composer -sdev create-project byjg/rest-reference-architecture ~/tutorial master
```

### Setup Configuration

The installation will prompt you for configuration details:

```text
> Builder\PostCreateScript::run
========================================================
 Setup Project
 Answer the questions below
========================================================

Project Directory: ~/tutorial
PHP Version [8.4]: 8.4
Project namespace [MyRest]: Tutorial
Composer name [me/myrest]:
MySQL connection DEV [mysql://root:mysqlp455w0rd@mysql-container/mydb]:
Timezone [UTC]:
Install Examples [Yes]: Yes
Press <ENTER> to continue
```

#### Configuration Options

- **PHP Version**: The PHP version for your Docker container (8.1, 8.2, 8.3, 8.4)
- **Project namespace**: Your application's root namespace (must be CamelCase, e.g., `MyApp`, `Tutorial`)
- **Composer name**: Package name in `vendor/package` format (e.g., `me/myrest`)
- **MySQL connection DEV**: Database connection string for development environment
- **Timezone**: Server timezone (e.g., `UTC`, `America/New_York`, `Europe/London`)
- **Install Examples**: Whether to include example code (Dummy, Sample classes)
  - **Yes** (default): Includes example implementations to help you learn
    - `DummyActiveRecord` - ActiveRecord pattern example
    - `Dummy` - Repository pattern example
    - `DummyHex` - Hexadecimal ID example
    - `Sample` and `SampleProtected` - Basic REST endpoints
  - **No**: Clean project with only the base `users` table and authentication

**Tip**: To access the MySQL container locally, add this to your `/etc/hosts` file:
```
127.0.0.1  mysql-container
```

## Running the Project

```shell
cd ~/tutorial
docker compose -f docker-compose-dev.yml up -d
```

## Database Setup

```shell
# Create a fresh database (warning: destroys existing data)
APP_ENV=dev composer run migrate -- reset --yes

# Alternative if local PHP isn't configured:
# export CONTAINER_NAME=myrest  # second part of your composer name
# docker exec -it $CONTAINER_NAME composer run migrate -- reset --yes
```

Expected output:
```text
> Builder\Scripts::migrate
> Command: reset
Doing reset, 0
Doing migrate, 1
```

## Verify Installation

### If You Installed Examples

```shell script
curl http://localhost:8080/sample/ping
```

Expected response:
```json
{"result":"pong"}
```

### If You Skipped Examples

The project is ready! You can start by:
- Creating your first table following the [getting started tutorial](getting_started_01_create_table.md)
- Accessing the API documentation at http://localhost:8080/docs
- The `users` table and authentication endpoints are already available

## Run Tests

```shell script
APP_ENV=dev composer run test
# OR: docker exec -it $CONTAINER_NAME composer run test
```

**Note**: If you chose not to install examples, the project will only include authentication tests. Example tests (Dummy, Sample) will not be present.

## Documentation

Access the Swagger documentation:
```shell script
open http://localhost:8080/docs
```

## Next Steps

Continue with [creating a new table and CRUD operations](getting_started_01_create_table.md).

---

**[← Previous: Service Layer](services.md)** | **[Next: Add a New Table →](getting_started_01_create_table.md)**
