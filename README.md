# PHP REST Reference Architecture

[![Sponsor](https://img.shields.io/badge/Sponsor-%23ea4aaa?logo=githubsponsors&logoColor=white&labelColor=0d1117)](https://github.com/sponsors/byjg)
[![Build Status](https://github.com/byjg/php-rest-reference-architecture/actions/workflows/build-app-image.yml/badge.svg?branch=6.0)](https://github.com/byjg/php-rest-reference-architecture/actions/workflows/build-app-image.yml)
[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](http://opensource.byjg.com)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/php-rest-reference-architecture)
[![GitHub license](https://img.shields.io/github/license/byjg/php-rest-reference-architecture.svg)](https://opensource.byjg.com/license/)
[![GitHub release](https://img.shields.io/github/release/byjg/php-rest-reference-architecture.svg)](https://github.com/byjg/php-rest-reference-architecture/releases)

**Production-ready PHP REST API boilerplate** that lets you focus on building your business logic, not the infrastructure.

## Why Use This?

Stop wasting time configuring infrastructure. This template provides everything you need to build professional REST APIs:

- ✅ **Start coding in minutes** - Not hours or days
- ✅ **Production-ready** - Security, authentication, and best practices built-in
- ✅ **Code generator** - Automatically create CRUD operations from database tables
- ✅ **Two architectural patterns** - Choose between Repository or ActiveRecord
- ✅ **OpenAPI documentation** - Auto-generated, always in sync
- ✅ **Fully tested** - Includes a functional test suite
- ✅ **Docker-ready** - Containerized development and deployment

## Quick Start

```bash
# Create your project
composer -sdev create-project byjg/rest-reference-architecture my-api ^6.0

# Start containers
cd my-api
docker compose -f docker-compose.yml up -d

# Run migrations
composer migrate -- --env=dev reset

# Your API is ready!
curl http://localhost:8080/sample/ping
```

**📚 [Complete Getting Started Guide →](docs/getting_started.md)**

## Architecture Overview

```mermaid
mindmap
  (("Reference Architecture"))
    ("PSR Standards")
      ("WebRequests")
      ("Container & Dependency Injection")
      ("Cache")
    ("Authentication & Authorization")
    ("Decoupled Code")
    ("Database")
      ("ORM Integration")
      ("Migration")
      ("Routing")
    ("OpenAPI Integration")
      ("Rest Methods")
      ("Contract Testing")
      ("Documentation")
    ("Error Handling")
```

## Key Features

### 🚀 Code Generation
Automatically generate Models, Repositories, Services, REST Controllers, and Tests from your database schema.

```bash
composer codegen -- --env=dev --table=users all --save
```

**📚 [Code Generator Documentation →](docs/code_generator.md)**

### 🏗️ Two Architectural Patterns

**Repository Pattern** (default)
- Clean separation of concerns
- Service layer for business logic
- Full dependency injection

**ActiveRecord Pattern**
- Rapid prototyping
- Less boilerplate
- Direct database access from models

**📚 [Choose Your Pattern →](docs/code_generator.md#what-it-generates)**

### 🔐 Authentication & Authorization Built-in

- JWT-based authentication
- Role-based access control (RBAC)
- Secure by default
- Ready-to-use login endpoints

**📚 [Authentication Guide →](docs/login.md)**

### 📖 OpenAPI Integration

- Auto-generated documentation
- Interactive API explorer (Swagger UI)
- Always synchronized with your code
- Contract testing support

**📚 [REST API Documentation →](docs/rest.md)**

### 🗄️ Database Management

**Migrations**
- Version control your database schema
- Up/down migration support
- Zero-downtime deployments

**📚 [Migration Guide →](docs/migration.md)**

**ORM Integration**
- MicroORM for lightweight data access
- Query builder
- Relationship mapping

**📚 [ORM Documentation →](docs/orm.md)**

### 🧪 Testing Built-in

- Functional test suite included
- Test helpers and fixtures
- OpenAPI contract testing
- Supports custom test scenarios

**📚 [Testing Guide →](docs/functional_test.md)**

### 🐳 Docker Ready

- Pre-configured Docker setup
- Development and production configurations
- MySQL, PHP-FPM, and Nginx
- One command to start

### 🔧 Scriptify - Interactive Development

- **Interactive PHP Terminal**: REPL with your project's autoloader
- **CLI Script Runner**: Execute any PHP method from command line
- **Service Management**: Install PHP classes as system daemons
- Quick prototyping and debugging

```bash
composer terminal  # Start interactive PHP shell
```

**📚 [Scriptify Guide →](docs/scriptify.md)**

### ⚙️ Modern PHP Standards

Implements PSR standards:
- PSR-7: HTTP Message Interface
- PSR-11: Container Interface
- PSR-6 & PSR-16: Cache Interface
- And more...

**📚 [PSR-11 Container →](docs/psr11.md)** | **[Dependency Injection →](docs/psr11_di.md)**

## What's Included

| Feature              | Description                        | Documentation                      |
|----------------------|------------------------------------|------------------------------------|
| **Code Generator**   | Generate CRUD from database tables | [→ Docs](docs/code_generator.md)   |
| **REST API**         | OpenAPI-documented endpoints       | [→ Docs](docs/rest.md)             |
| **Authentication**   | JWT with role-based access         | [→ Docs](docs/login.md)            |
| **Database**         | Migrations + ORM                   | [→ Docs](docs/migration.md)        |
| **Testing**          | Functional test suite              | [→ Docs](docs/functional_test.md)  |
| **Service Layer**    | Business logic separation          | [→ Docs](docs/services.md)         |
| **Scriptify**        | Interactive terminal & CLI scripts | [→ Docs](docs/scriptify.md)        |
| **Unattended Setup** | CI/CD friendly installation        | [→ Docs](docs/unattended_setup.md) |

## Documentation

### Getting Started
1. **[Installation & Setup](docs/getting_started.md)** – Install the template, configure environments, and review prerequisites.
2. **[Create Your First Table](docs/getting_started_01_create_table.md)** – Define your first migration and schema.
3. **[Add Fields](docs/getting_started_02_add_new_field.md)** – Safely evolve existing tables.
4. **[Create REST Endpoints](docs/getting_started_03_create_rest_method.md)** – Generate REST handlers from your tables.

### Build Your API
- **[Code Generator](docs/code_generator.md)** – Automate models, repositories, services, controllers, and tests.
- **[REST API](docs/rest.md)** – Implement endpoints that stay in sync with OpenAPI contracts.
- **[Authentication](docs/login.md)** – Configure JWT login flows and RBAC enforcement.
- **[Database Migration](docs/migration.md)** – Version and run schema migrations in every environment.
- **[ORM](docs/orm.md)** – Use MicroORM for repository and ActiveRecord patterns.
- **[Service Layer](docs/services.md)** – Organize business logic and transaction boundaries.
- **[Service Patterns](docs/service-patterns.md)** – Adopt advanced orchestration, validation, and DTO patterns.
- **[Repository Patterns](docs/repository-advanced.md)** – Implement complex queries, UUID handling, and filtering helpers.
- **[Attributes System](docs/attributes.md)** – Apply RequireRole, ValidateRequest, and custom attributes to controllers.
- **[Traits Reference](docs/traits.md)** – Reuse timestamp and soft-delete helpers inside models.
- **[Template Customization](docs/templates.md)** – Tailor the generator templates to match your coding standards.

### Architecture & Operations
- **[Configuration Deep Dive](docs/configuration-advanced.md)** – Layer configurations, secrets, and environment overrides.
- **[Architecture Decisions](docs/architecture-decisions.md)** – Decide when to use Repository or ActiveRecord implementations.
- **[PSR-11 Container](docs/psr11.md)** – Understand the default container bindings that power `src/`.
- **[Dependency Injection](docs/psr11_di.md)** – Wire repositories, services, and factories through the container.
- **[Scriptify](docs/scriptify.md)** – Use the REPL, CLI runner, and service manager utilities.
- **[Unattended Setup](docs/unattended_setup.md)** – Automate installs for CI/CD pipelines.
- **[Windows Setup](docs/windows.md)** – Follow the WSL/Windows specific checklist.

### Testing & Quality
- **[Complete Testing Guide](docs/testing-guide.md)** – Unit, integration, and contract testing reference.
- **[Functional Tests](docs/functional_test.md)** – Use `FakeApiRequester` and fixtures for end-to-end coverage.
- **[JWT Authentication Advanced](docs/jwt-advanced.md)** – Extend tokens with custom claims and refresh logic.
- **[Error Handling](docs/error-handling.md)** – Map exceptions to HTTP responses and logging patterns.

## Real-World Example

```bash
# 1. Create database table
cat > db/migrations/up/00002-create-products.sql << 'EOF'
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
EOF

# 2. Run migration
composer migrate -- --env=dev update

# 3. Generate all code
composer codegen -- --env=dev --table=products all --save

# 4. Your CRUD API is ready!
curl http://localhost:8080/products
```

You just created a complete CRUD API with:
- ✅ Model with validation
- ✅ Repository for data access
- ✅ Service for business logic
- ✅ REST controller with GET, POST, PUT endpoints
- ✅ Functional tests
- ✅ OpenAPI documentation
- ✅ JWT authentication

## Requirements

- PHP 8.3+ (8.5 recommended)
- Docker & Docker Compose (optional but recommended)
- Composer
- Git

## Support & Community

- 📖 **[Full Documentation](docs/getting_started.md)**
- 🐛 **[Report Issues](https://github.com/byjg/php-rest-reference-architecture/issues)**
- 💡 **[Request Features](https://github.com/byjg/php-rest-reference-architecture/issues)**
- 🌐 **[ByJG Open Source](http://opensource.byjg.com)**

## Not a Framework

This is a **template**, not a framework. You own the code:
- ✅ Full control over every file
- ✅ No vendor lock-in
- ✅ Customize anything you need
- ✅ Remove what you don't need

## License

This project is open source. See [LICENSE](https://opensource.byjg.com/opensource/licensing.html) for details.

## Dependencies

**📚 [Complete Component Dependency Graph →](docs/components-dependency.md)**

---

**[Open source ByJG](http://opensource.byjg.com)**
