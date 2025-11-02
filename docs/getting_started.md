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
composer create-project byjg/rest-reference-architecture ~/tutorial ^5.0

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
```

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

```shell script
curl http://localhost:8080/sample/ping
```

Expected response:
```json
{"result":"pong"}
```

## Run Tests

```shell script
APP_ENV=dev composer run test
# OR: docker exec -it $CONTAINER_NAME composer run test
```

## Documentation

Access the Swagger documentation:
```shell script
open http://localhost:8080/docs
```

## Next Steps

Continue with [creating a new table and CRUD operations](getting_started_01_create_table.md).

---

**[← Previous: Service Layer](services.md)** | **[Next: Add a New Table →](getting_started_01_create_table.md)**
