# Boilerplate Project Template for RESTFul API

[![Build Status](https://github.com/byjg/php-rest-template/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/byjg/php-rest-template/actions/workflows/phpunit.yml)
[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](http://opensource.byjg.com)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/php-rest-template/)
[![GitHub license](https://img.shields.io/github/license/byjg/php-rest-template.svg)](https://opensource.byjg.com/opensource/licensing.html)
[![GitHub release](https://img.shields.io/github/release/byjg/php-rest-template.svg)](https://github.com/byjg/php-rest-template/releases/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/byjg/php-rest-template/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/byjg/php-rest-template/?branch=master)

This project is a boilerplate for create Rest Applications API Ready to Use with the best techniques to improve your productivity.

## Features

This project install the follow components (click on the link for more details):

- [Rest Methods API integrated with OpenAPI](docs/rest.md)
- [Functional Unit Tests of your Rest Method API](docs/functional_tests.md)
- [PSR-11 Container and different environments](docs/psr11.md)
- [Dependency Injection](docs/psr11_di.md)
- [Login Integration with JWT](docs/login.md)
- [Database Migration](docs/migration.md)
- [Database ORM](docs/orm.md)

## Install

### Requirements

This project requires in order to run:

- PHP
- composer

### Create a Project from the Stable Release

```bash
composer create-project byjg/resttemplate YOURPATH 5.0.*
```

### Create a Project from Development Release

```bash
composer -sdev create-project byjg/resttemplate YOURPATH master
```

Use instead of `master` branch the feature branch version like `5.0.0.x-dev` 


----
[Open source ByJG](http://opensource.byjg.com)
