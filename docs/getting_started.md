# Getting Started

## Requirements

Docker engine, PHP and an IDE.

You'll need PHP 8.1 or higher installed in your machine. Preferrable the same version as you want to work with your project. 

Required PHP extensions:
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

```bash
mkdir ~/tutorial
composer create-project byjg/rest-reference-architecture ~/tutorial 4.9.*
```

or the latest development version:

```bash
mkdir ~/tutorial
composer -sdev create-project byjg/rest-reference-architecture ~/tutorial master
```

This process will ask some questions to setup your project. You can use the following below as a guide:

```text
> Builder\PostCreateScript::run
========================================================
 Setup Project
 Answer the questions below
========================================================

Project Directory: /tmp/tutorial
PHP Version [7.4]: 8.1
Project namespace [MyRest]: Tutorial
Composer name [me/myrest]: 
MySQL connection DEV [mysql://root:mysqlp455w0rd@mysql-container/mydb]: 
Timezone [UTC]: 
Press <ENTER> to continue
```

Tip: The docker composer will create MySQL container named as `mysql-container` ([ref](https://github.com/byjg/php-rest-template/blob/master/docker-compose-dev.yml#L20)). 
If you want to be able to access your MySQL container from your machine you need to add the following entry in your `/etc/hosts` file:

```
127.0.0.1  mysql-container
```


## Running the Project

```bash
cd ~/tutorial
docker-compose -f docker-compose-dev.yml up -d
```

## Creating the Database

```bash
# Important this will destroy ALL DB data and create a fresh new database based on the migration
APP_ENV=dev composer run migrate -- reset --yes

# *IF* your local PHP is not properly setup you can run this instead:
# export CONTAINER_NAME=# it is the second part of the composer name. e.g. me/myrest, it should be "myrest"
# docker exec -it $CONTAINER_NAME composer run migrate -- reset --yes
```

The result should be:

```text
> Builder\Scripts::migrate
> Command: reset
Doing reset, 0
Doing migrate, 1
```

## Testing the Project

```bash
curl http://localhost:8080/sample/ping
```

The result:

```json
{"result":"pong"}
```

## Running the Unit Tests

```bash
APP_ENV=dev composer run test    # Alternatively you can run `./vendor/bin/phpunit`

# OR: docker exec -it $CONTAINER_NAME composer run test
```

## Accessing the Swagger Documentation

```bash
open http://localhost:8080/docs
```

## Continue the Tutorial

You can continue this tutorial by following the next step: [creating a new table and crud](getting_started_01_create_table.md).
