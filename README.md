# Rest Template

This project creates a starter project for REST Api

This project install the follow components:
- PSR11 Container dependency
- A JWT Authentication
- Pre-configured for different environments (DEV, HOMOLOG, LIVE, etc)
- Database
- Docker for build your project 

## Install

```bash
composer create-project byjg/resttemplate YOURPATH 1.0.*
```

## How to use

### Start

After the command create-project is executed some questions will be asked for setup your new project. 

### Containers

This project uses a PSR11 implementation for container. 
The implementation is from [byjg/config](https://github.com/byjg/config). 

Start editing from "config/config-dev"

### Build

The build process will create a docker container with the PHP+NGINX and the code necessary to run your project.

The ready to use command is:

```bash
APPLICATION_ENV=dev composer build
```

#### Build TL;DR

The build process uses the configuration environment defined in the Container to create a docker instance. 

The process for build is:
- Read the PSR11 Container with the specific environment;
- Copy the Dockerfile template from 'docker/Dockerfile' to the workdir
- Customize the Dockerfile at the Workdir with PSR11 "DOCKERFILE" variable;
- Run the PSR11 "DOCKER_BEFORE_BUILD" variable
- Run the PSR11 "DOCKER_DEPLOY_COMMAND" variable

Your PSR11 Container must have the follow variables:

- DOCKER_IMAGE: The docker image name;
- DOCKERFILE: an array with specific commands for the current environment. Basically
the build process will copy the docker template file from 'docker/Dockerfile' and replace the 
string comment `##---ENV-SPECIFICS-HERE` with the commands defined here;
- DOCKER_DEPLOY_COMMAND: The commands used to deploy your docker image. Maybe a docker run command or 
a docker push or everything else.;
- DOCKER_BEFORE_BUILD' => The commands before start the build image. For example the grunt command or a minifier; 

Variables
- %env% - Your current environment
- %workdir% - The root workdir
- %image% - The image name
- %container% - The docker container image name 


### Migrate database

```bash
APPLICATION_ENV=dev composer migrate -- update
```

#### Database TL;DR

##### Migration

The migration process uses the [byjg/migration](https://github.com/byjg/migration)

Basically there are migration scripts located at "%workdir%/db".

The initial file is "base.sql" and the migration are "%workdir%/db/up/1.sql", "%workdir%/db/up/2.sql" and so on. 

The basic migrations command are:
- reset: Will reset the database starting from "base.sql" and apply an migrations starting from 1.sql.
- update: Will update the database until the most recent version existing in the migration;
- update --up-to=n: Will update the database until the version 'n' existing in the migration;

The connection is in the Psr11 container variable 'DBDRIVER_CONNECTION';

##### ORM

The ORM uses the [byjg/micro-orm](https://github.com/byjg/micro-orm)

There are a basic example in "%workdir%/src/project/Repository/DummyRepository". 
The defintion in the Psr11 container variable 'DUMMY_TABLE'

