---
sidebar_position: 90
---

# Database ORM

The project uses [byjg/micro-orm](https://github.com/byjg/micro-orm) for database operations.

## Creating a Repository

Start by creating a class that extends `BaseRepository` and defines the table name and primary key:

```php
<?php

namespace RestReferenceArchitecture\Repository;

use ByJG\MicroOrm\Mapper;
use ByJG\MicroOrm\Repository;
use ByJG\AnyDataset\Db\DbDriverInterface;
use RestReferenceArchitecture\Model\YourModel;

class YourRepository extends BaseRepository
{
    public function __construct(DbDriverInterface $dbDriver)
    {
        $mapper = new Mapper(
            YourModel::class,    // Model class
            'table_name',        // Table name
            'id'                 // Primary key field
        );

        $this->repository = new Repository($dbDriver, $mapper);
    }
}
```

## Basic CRUD Operations

The repository provides basic CRUD methods:

```php
<?php

// Get a single record by primary key
$model = $repository->get($id);

// Get all records
$models = $repository->getAll();

// Save (insert or update)
$repository->save($model);

// Delete a record
$repository->delete($model);
```

## Custom Queries

Create custom query methods in your repository:

```php
<?php

use ByJG\MicroOrm\Query;

public function getByName(string $name): ?YourModel
{
    $query = Query::getInstance()
        ->table('table_name')
        ->where('table_name.name = :name', ['name' => $name]);

    return $this->repository->getByQuery($query);
}

public function getActiveRecords(): array
{
    $query = Query::getInstance()
        ->table('table_name')
        ->where('status = :status', ['status' => 'active'])
        ->orderBy(['created_at' => 'DESC']);

    return $this->repository->getByQuery($query, Mapper::RESULT_ARRAY);
}
```

## Using Services

:::tip Best Practice
Instead of using repositories directly in your REST controllers, use the **Service Layer**. Services handle business logic and can orchestrate multiple repositories.
:::

Example using a service:

```php
<?php

use ByJG\Config\Config;
use RestReferenceArchitecture\Service\YourService;

// In your REST controller
$service = Config::get(YourService::class);
$model = $service->getOrFail($id);
```

See the [Service Layer](services.md) documentation for more details.
