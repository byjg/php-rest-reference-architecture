---
sidebar_position: 120
---

# Advanced Repository Patterns

This guide covers advanced features of the Repository layer, including complex queries, UUID handling, transactions, and custom query methods.

## Table of Contents

- [Overview](#overview)
- [BaseRepository Features](#baserepository-features)
- [Advanced Querying](#advanced-querying)
- [UUID Handling](#uuid-handling)
- [Custom Query Methods](#custom-query-methods)
- [Transaction Management](#transaction-management)
- [Performance Optimization](#performance-optimization)
- [ActiveRecord Pattern](#activerecord-pattern)

## Overview

The `BaseRepository` class provides a foundation for data access with support for:

- CRUD operations
- Pagination and filtering
- UUID generation and conversion
- Query builder integration
- Transaction support
- Custom query methods

**Location**: `src/Repository/BaseRepository.php`

## BaseRepository Features

### Basic Methods

All repositories extending `BaseRepository` have these methods:

```php
// Get single entity by ID
$dummy = $repository->get($id);

// Get the underlying ByJG Repository
$ormRepository = $repository->getRepository();

// Get the Mapper
$mapper = $repository->getMapper();

// Get read executor
$executor = $repository->getExecutor();

// Get write executor (for master/slave setups)
$executorWrite = $repository->getExecutorWrite();

// Create empty model instance
$model = $repository->model();
```

### Example Repository

```php
<?php

namespace RestReferenceArchitecture\Repository;

use ByJG\MicroOrm\Mapper;
use ByJG\MicroOrm\Repository;
use RestReferenceArchitecture\Model\Dummy;

class DummyRepository extends BaseRepository
{
    public function __construct()
    {
        $mapper = new Mapper(
            Dummy::class,
            'dummy',
            'id'
        );

        $this->repository = new Repository(
            Config::get(\ByJG\AnyDataset\Db\DbDriverInterface::class),
            $mapper
        );
    }
}
```

## Advanced Querying

### The listQuery() Method

The `listQuery()` method provides powerful filtering, pagination, and ordering capabilities:

**Location**: `src/Repository/BaseRepository.php:109`

```php
public function listQuery(
    $tableName = null,
    $fields = [],
    $page = 0,
    $size = 20,
    $orderBy = null,
    $filter = null
): Query
```

#### Parameters

- **`tableName`**: Override the default table (useful for joins)
- **`fields`**: Array of fields to select (default: all fields)
- **`page`**: Page number (0-indexed)
- **`size`**: Results per page
- **`orderBy`**: String or array of ORDER BY clauses
- **`filter`**: Array of WHERE conditions

#### Example: Basic Pagination

```php
$query = $repository->listQuery(
    page: 0,      // First page
    size: 50,     // 50 results
    orderBy: 'name ASC'
);

$results = $repository->getRepository()->getByQuery($query);
```

#### Example: Multiple Order By

```php
$query = $repository->listQuery(
    page: 2,
    size: 25,
    orderBy: ['created_at DESC', 'name ASC']
);
```

#### Example: Filtering

```php
// Filter format: [field_expression, value]
$filter = [
    ['name = [[name]]', ['name' => 'John']],
    ['age > [[min_age]]', ['min_age' => 18]],
    ['status = [[status]]', ['status' => 'active']]
];

$query = $repository->listQuery(
    page: 0,
    size: 20,
    orderBy: 'created_at DESC',
    filter: $filter
);
```

#### Example: Custom Fields

```php
// Only select specific fields
$query = $repository->listQuery(
    fields: ['id', 'name', 'email'],
    page: 0,
    size: 100
);
```

### The listGeneric() Method

Query arbitrary tables dynamically without a mapper:

**Location**: `src/Repository/BaseRepository.php:99`

```php
public function listGeneric(
    $tableName,
    $fields = [],
    $page = 0,
    $size = 20,
    $orderBy = null,
    $filter = null
)
```

#### Example: Query Related Table

```php
// Query a join table or related table
$userRoles = $repository->listGeneric(
    tableName: 'user_roles',
    fields: ['user_id', 'role_id', 'granted_at'],
    filter: [
        ['user_id = [[uid]]', ['uid' => $userId]]
    ],
    orderBy: 'granted_at DESC'
);

// Returns array of associative arrays (not model objects)
foreach ($userRoles as $row) {
    echo $row['role_id'];
}
```

#### Example: Aggregation Query

```php
// Count records by status
$statusCounts = $repository->listGeneric(
    tableName: 'orders',
    fields: ['status', 'COUNT(*) as count'],
    filter: [
        ['created_at > [[date]]', ['date' => '2024-01-01']]
    ],
    orderBy: 'count DESC'
);
```

### Custom Query with getByQuery()

Build completely custom queries using the Query builder:

```php
use ByJG\MicroOrm\Query;

// Complex query with joins
$query = Query::getInstance()
    ->table('dummy', 'd')
    ->join('user', 'u', 'd.user_id = u.id')
    ->fields(['d.*', 'u.name as user_name'])
    ->where('d.status = :status', ['status' => 'active'])
    ->where('u.role = :role', ['role' => 'admin'])
    ->orderBy(['d.created_at DESC'])
    ->limit(0, 50);

$results = $repository->getByQuery($query);
```

### Advanced Filter Examples

#### IN Clause

```php
$filter = [
    ['status IN (:statuses)', ['statuses' => ['active', 'pending']]]
];
```

#### LIKE Search

```php
$filter = [
    ['name LIKE :search', ['search' => '%john%']]
];
```

#### Multiple Conditions with OR

```php
use ByJG\MicroOrm\Query;

$query = Query::getInstance()
    ->table($repository->getMapper()->getTable())
    ->where('(status = :status1 OR status = :status2)', [
        'status1' => 'active',
        'status2' => 'pending'
    ])
    ->where('created_at > :date', ['date' => '2024-01-01']);

$results = $repository->getByQuery($query);
```

#### Date Range

```php
$filter = [
    ['created_at >= :start', ['start' => '2024-01-01']],
    ['created_at <= :end', ['end' => '2024-12-31']]
];
```

## UUID Handling

The BaseRepository provides powerful UUID handling for binary UUID storage.

### Generate New UUID

**Location**: `src/Repository/BaseRepository.php:148`

#### Static Method

```php
// Generate UUID string
$uuid = BaseRepository::getUuid();
// Returns: "550E8400-E29B-41D4-A716-446655440000"
```

#### Closure for Auto-Generation

```php
use ByJG\MicroOrm\FieldMapping;

// Use in Mapper for auto-generation on insert
$mapper->addFieldMapping(
    FieldMapping::create('id')
        ->withDefaultValue(DummyRepository::getClosureNewUUID())
);
```

**Location**: `src/Repository/BaseRepository.php:148`

This returns a closure that generates binary UUID literals:

```php
public static function getClosureNewUUID(): Closure
{
    return function () {
        return new Literal("X'" . Config::get(DbDriverInterface::class)
            ->getScalar("SELECT hex(uuid_to_bin(uuid()))") . "'");
    };
}
```

### Binary UUID Conversion

Store UUIDs efficiently as binary (16 bytes) instead of strings (36 bytes):

**Location**: `src/Repository/BaseRepository.php:176`

```php
protected function setClosureFixBinaryUUID(
    ?Mapper $mapper,
    $binPropertyName = 'id',
    $uuidStrPropertyName = 'uuid'
): FieldMapping
```

#### Example: Binary UUID Field

```php
class DummyHexRepository extends BaseRepository
{
    public function __construct()
    {
        $mapper = new Mapper(
            DummyHex::class,
            'dummy_hex',
            'id'
        );

        // Convert between binary storage and string representation
        $this->setClosureFixBinaryUUID($mapper, 'id', 'uuid');

        // Auto-generate UUID on insert
        $mapper->addFieldMapping(
            FieldMapping::create('id')
                ->withDefaultValue(self::getClosureNewUUID())
        );

        $this->repository = new Repository(
            Config::get(\ByJG\AnyDataset\Db\DbDriverInterface::class),
            $mapper
        );
    }
}
```

#### How It Works

1. **On Insert/Update**: Converts UUID string to binary literal
2. **On Select**: Converts binary back to UUID string format
3. **Property Mapping**: Maps binary `id` field to string `uuid` property

#### Model Setup

```php
class DummyHex
{
    #[FieldAttribute(primaryKey: true, fieldName: "id")]
    protected string|null $id = null;

    // Virtual property for UUID string representation
    protected string|null $uuid = null;

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): static
    {
        $this->uuid = $uuid;
        $this->id = $uuid; // Sync to id
        return $this;
    }
}
```

### Using HexUuidLiteral Directly

```php
use ByJG\MicroOrm\Literal\HexUuidLiteral;

// Get by UUID (handles conversion automatically)
$dummy = $repository->get($uuidString);

// In BaseRepository::get()
public function get($itemId)
{
    return $this->repository->get(HexUuidLiteral::create($itemId));
}
```

## Custom Query Methods

Add domain-specific query methods to your repositories or models.

### In Repository Pattern

```php
class DummyRepository extends BaseRepository
{
    public function findByStatus(string $status): array
    {
        $query = Query::getInstance()
            ->table($this->repository->getMapper()->getTable())
            ->where('status = :status', ['status' => $status])
            ->orderBy(['created_at DESC']);

        return $this->repository->getByQuery($query);
    }

    public function findByDateRange(string $start, string $end): array
    {
        $query = Query::getInstance()
            ->table($this->repository->getMapper()->getTable())
            ->where('created_at >= :start', ['start' => $start])
            ->where('created_at <= :end', ['end' => $end]);

        return $this->repository->getByQuery($query);
    }

    public function countByStatus(string $status): int
    {
        $result = $this->listGeneric(
            tableName: $this->repository->getMapper()->getTable(),
            fields: ['COUNT(*) as count'],
            filter: [
                ['status = :status', ['status' => $status]]
            ]
        );

        return (int) $result[0]['count'];
    }

    public function findRecent(int $limit = 10): array
    {
        $query = Query::getInstance()
            ->table($this->repository->getMapper()->getTable())
            ->orderBy(['created_at DESC'])
            ->limit(0, $limit);

        return $this->repository->getByQuery($query);
    }
}
```

### In ActiveRecord Pattern

Add static query methods directly to your model:

**Location**: `src/Model/DummyActiveRecord.php:109`

```php
class DummyActiveRecord
{
    use ActiveRecord;

    /**
     * Find records by name
     *
     * @param mixed $name
     * @return null|DummyActiveRecord[]
     */
    public static function getByName($name): ?array
    {
        $query = Query::getInstance()
            ->table(self::$repository->getMapper()->getTable(), 'alias')
            ->where('alias.name = :value', ['value' => $name]);

        return self::query($query);
    }

    /**
     * Find active records
     *
     * @return null|DummyActiveRecord[]
     */
    public static function findActive(): ?array
    {
        $query = Query::getInstance()
            ->table(self::$repository->getMapper()->getTable())
            ->where('status = :status', ['status' => 'active'])
            ->orderBy(['created_at DESC']);

        return self::query($query);
    }

    /**
     * Get single record by unique field
     *
     * @param string $email
     * @return DummyActiveRecord|null
     */
    public static function getByEmail(string $email): ?DummyActiveRecord
    {
        $query = Query::getInstance()
            ->table(self::$repository->getMapper()->getTable())
            ->where('email = :email', ['email' => $email]);

        $results = self::query($query);
        return $results[0] ?? null;
    }
}
```

#### Usage

```php
// Call static methods directly on the model
$users = DummyActiveRecord::getByName('John');
$active = DummyActiveRecord::findActive();
$user = DummyActiveRecord::getByEmail('john@example.com');
```

## Transaction Management

### Using the Executor

```php
$executor = $repository->getExecutorWrite();

try {
    $executor->beginTransaction();

    // Perform multiple operations
    $repository->save($model1);
    $repository->save($model2);
    $repository->save($model3);

    $executor->commitTransaction();
} catch (\Exception $e) {
    $executor->rollbackTransaction();
    throw $e;
}
```

### Multi-Repository Transactions

```php
use ByJG\Config\Config;

$dummyRepo = Config::get(DummyRepository::class);
$userRepo = Config::get(UserRepository::class);

// Ensure both use the same executor
$executor = $dummyRepo->getExecutorWrite();

try {
    $executor->beginTransaction();

    $user = $userRepo->save($userData);
    $dummy = $dummyRepo->save($dummyData);

    // Link them
    $dummy->setUserId($user->getId());
    $dummyRepo->save($dummy);

    $executor->commitTransaction();
} catch (\Exception $e) {
    $executor->rollbackTransaction();
    throw $e;
}
```

### Transaction Wrapper

Create a helper for cleaner transaction handling:

```php
trait TransactionHelper
{
    protected function transaction(callable $callback)
    {
        $executor = $this->getExecutorWrite();

        try {
            $executor->beginTransaction();
            $result = $callback($executor);
            $executor->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $executor->rollbackTransaction();
            throw $e;
        }
    }
}

// Usage in repository
class DummyRepository extends BaseRepository
{
    use TransactionHelper;

    public function createWithRelated($dummyData, $relatedData)
    {
        return $this->transaction(function($executor) use ($dummyData, $relatedData) {
            $dummy = $this->save($dummyData);
            // Save related data
            return $dummy;
        });
    }
}
```

## Performance Optimization

### Eager Loading with Joins

```php
public function listWithUser(): array
{
    $query = Query::getInstance()
        ->table($this->repository->getMapper()->getTable(), 'd')
        ->join('user', 'u', 'd.user_id = u.id')
        ->fields([
            'd.*',
            'u.name as user_name',
            'u.email as user_email'
        ]);

    return $this->repository->getByQuery($query);
}
```

### Selective Field Loading

```php
// Only load fields you need
$query = Query::getInstance()
    ->table($this->repository->getMapper()->getTable())
    ->fields(['id', 'name', 'status'])  // Don't load large text fields
    ->where('status = :status', ['status' => 'active']);
```

### Batch Operations

```php
public function bulkInsert(array $models): void
{
    $executor = $this->getExecutorWrite();

    try {
        $executor->beginTransaction();

        foreach ($models as $model) {
            $this->repository->save($model);
        }

        $executor->commitTransaction();
    } catch (\Exception $e) {
        $executor->rollbackTransaction();
        throw $e;
    }
}
```

### Caching Results

```php
use Psr\SimpleCache\CacheInterface;

class DummyRepository extends BaseRepository
{
    protected CacheInterface $cache;

    public function findByIdCached($id)
    {
        $key = "dummy:{$id}";

        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $dummy = $this->get($id);
        $this->cache->set($key, $dummy, 3600); // 1 hour

        return $dummy;
    }

    public function save($model, $updateConstraint = null): mixed
    {
        $result = parent::save($model, $updateConstraint);

        // Invalidate cache
        $key = "dummy:{$result->getId()}";
        $this->cache->delete($key);

        return $result;
    }
}
```

## ActiveRecord Pattern

The ActiveRecord pattern provides an alternative to the Repository pattern where models handle their own persistence.

### Basic ActiveRecord Usage

```php
use RestReferenceArchitecture\Model\DummyActiveRecord;

// Create
$dummy = new DummyActiveRecord();
$dummy->setName('John');
$dummy->setValue('Test');
$dummy->save();

// Read
$dummy = DummyActiveRecord::get(1);

// Update
$dummy->setValue('Updated');
$dummy->save();

// Delete
$dummy->delete();

// Query
$results = DummyActiveRecord::getByName('John');
```

### Adding Custom Methods

See [Custom Query Methods](#custom-query-methods) section above.

### When to Use ActiveRecord vs Repository

**Use ActiveRecord when:**
- Simple CRUD operations dominate
- Models are independent
- Rapid development is priority
- Small to medium projects

**Use Repository when:**
- Complex business logic
- Multiple data sources
- Need for testing isolation
- Large enterprise applications

See [Architecture Decision Guide](architecture-decisions.md) for detailed comparison.

## Advanced Examples

### Full-Text Search

```php
public function search(string $term): array
{
    $query = Query::getInstance()
        ->table($this->repository->getMapper()->getTable())
        ->where(
            'MATCH(name, description) AGAINST (:term IN BOOLEAN MODE)',
            ['term' => $term . '*']
        );

    return $this->repository->getByQuery($query);
}
```

### Hierarchical Queries

```php
public function getWithChildren(int $parentId): array
{
    $query = Query::getInstance()
        ->table($this->repository->getMapper()->getTable(), 'parent')
        ->join(
            $this->repository->getMapper()->getTable(),
            'child',
            'parent.id = child.parent_id'
        )
        ->where('parent.id = :id', ['id' => $parentId])
        ->fields(['parent.*', 'child.id as child_id', 'child.name as child_name']);

    return $this->repository->getByQuery($query);
}
```

### Soft Deletes

```php
public function softDelete($model): void
{
    $model->setDeletedAt(date('Y-m-d H:i:s'));
    $this->repository->save($model);
}

public function listActive($page = 0, $size = 20): array
{
    $query = $this->listQuery(
        page: $page,
        size: $size,
        filter: [
            ['deleted_at IS NULL', []]
        ]
    );

    return $this->repository->getByQuery($query);
}
```

## Best Practices

1. **Use Type Hints**: Always type-hint return values and parameters
2. **Document Complex Queries**: Add PHPDoc comments explaining query logic
3. **Cache Expensive Queries**: Use PSR-16 cache for slow queries
4. **Index Database Fields**: Ensure WHERE/ORDER BY fields are indexed
5. **Validate Before Save**: Check constraints before database operations
6. **Use Transactions**: Group related operations in transactions
7. **Pagination**: Always paginate list queries
8. **Avoid N+1 Queries**: Use joins for related data

## Related Documentation

- [ORM Usage Guide](orm.md)
- [Service Patterns](service-patterns.md)
- [Architecture Decisions](architecture-decisions.md)
- [Testing Guide](testing-guide.md)
