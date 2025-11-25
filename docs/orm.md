---
sidebar_position: 90
---

# Database ORM

The reference architecture uses [byjg/micro-orm](https://github.com/byjg/micro-orm) with PHP 8 attributes. Your models declare the mapping, repositories receive the shared `DatabaseExecutor`, and `BaseRepository` supplies common helpers.

## 1. Model Mapping with Attributes

Annotate your models with `TableAttribute` / `FieldAttribute` (or the UUID variants) so Micro ORM knows how to persist each property.

```php
<?php

namespace RestReferenceArchitecture\Model;

use ByJG\MicroOrm\Attributes\FieldAttribute;
use ByJG\MicroOrm\Attributes\TableAttribute;

#[TableAttribute("dummy")]
class Dummy
{
    #[FieldAttribute(primaryKey: true, fieldName: "id")]
    protected ?int $id = null;

    #[FieldAttribute(fieldName: "field")]
    protected ?string $field = null;

    // getters/setters omitted for brevity
}
```

Need UUID support? Use `#[TableMySqlUuidPKAttribute]` + `#[FieldUuidAttribute]` as shown in `src/Model/DummyHex.php` and `src/Model/User.php`. They automatically read/write binary UUIDs via `HexUuidLiteral`.

## 2. Repository Definition

Repositories extend `RestReferenceArchitecture\Repository\BaseRepository`. Inject `ByJG\AnyDataset\Db\DatabaseExecutor` and hand it to `ByJG\MicroOrm\Repository`, pointing at your model class:

```php
<?php

namespace RestReferenceArchitecture\Repository;

use ByJG\AnyDataset\Db\DatabaseExecutor;
use ByJG\MicroOrm\Repository;
use RestReferenceArchitecture\Model\Dummy;

class DummyRepository extends BaseRepository
{
    public function __construct(DatabaseExecutor $executor)
    {
        $this->repository = new Repository($executor, Dummy::class);
    }
}
```

`BaseRepository` already exposes useful helpers:

```php
$dummy = $repository->get($id);
$list  = $repository->list(page: 0, size: 20);
$model = $repository->model();          // New instance of mapped entity
$repo  = $repository->getRepository();  // Direct access to ByJG\Repository
$mapper = $repository->getMapper();     // Underlying Mapper instance
```

Saving and deleting records delegates to Micro ORM while keeping literals in sync:

```php
$model = $repository->model()
    ->setField('hello world');

$saved = $repository->save($model);
$repository->delete($saved->getId());
```

## 3. Custom Queries

Use `ByJG\MicroOrm\Query` (or any `QueryBuilderInterface`) for filtered results:

```php
use ByJG\MicroOrm\Query;

class DummyRepository extends BaseRepository
{
    public function findByField(string $value): array
    {
        $query = Query::getInstance()
            ->table($this->getMapper()->getTable())
            ->where('field = :value', ['value' => $value])
            ->orderBy(['id DESC']);

        return $this->getRepository()->getByQuery($query);
    }
}
```

`listQuery()` (in `BaseRepository`) already builds paginated queries—pass filters, order clauses, and selected fields as needed. For raw arrays (instead of model hydration), call `listGeneric()` to run ad-hoc queries while reusing the same pagination helpers.

## 4. Working with UUIDs

Binary UUID columns are transparent when you rely on the attribute helpers:

- `#[TableMySqlUuidPKAttribute("dummy_hex")]` wires the table and default UUID generator.
- `#[FieldUuidAttribute(primaryKey: true)]` handles binary ⇄ string conversion automatically.
- `HexUuidLiteral::create($uuid)` lets you query by UUID strings without manual hex handling.

`BaseRepository::save()` normalizes any UUID `Literal` back to a formatted string so controllers/tests can keep using human-readable IDs.

## 5. Services and REST Controllers

Repositories are registered in `config/<env>/04-repositories.php` and injected into services (see `src/Service/DummyService.php`). Services orchestrate repositories, and REST controllers resolve services via the PSR-11 container. For deeper patterns (DTOs, filters, transactions), read [Repository Patterns](repository-advanced.md) and [Service Layer](services.md).
