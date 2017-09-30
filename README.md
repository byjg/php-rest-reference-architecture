# Rest Template

This project creates a starter project for REST Api

This project install the follow components:
- PSR11 Container dependency
- A JWT Authentication
- Pre-configured for different environments (DEV, HOMOLOG, LIVE, etc)
- Docker for build your project 

## Install

```bash
composer create-project byjg/resttemplate YOURPATH 1.0.*
```

## How to use

### Start

Replace 'RestTemplate' to your default namespace

### Containers

This project uses a PSR11 implementation for container. 
The implementation is from [byjg/config](https://github.com/byjg/config). 

Start editing from "config/config-dev"

### Build

```bash
APPLICATION_ENV=dev php build.php
```

