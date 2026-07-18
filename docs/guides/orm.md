---
sidebar_position: 150
title: ORM & Repository
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

#[TableAttribute("project")]
class Project
{
    #[FieldAttribute(primaryKey: true, fieldName: "id")]
    protected ?int $id = null;

    #[FieldAttribute(fieldName: "name")]
    protected ?string $name = null;

    #[FieldAttribute(fieldName: "description")]
    protected ?string $description = null;

    // getters/setters omitted for brevity
}
```

Need UUID support? Use `#[TableMySqlUuidPKAttribute]` + `#[FieldUuidAttribute]` as shown in `api/src/Model/Task.php` and `api/src/Model/User.php`. They automatically read/write binary UUIDs via `HexUuidLiteral`.

## 2. Repository Definition

Repositories extend `ByJG\Gluo\Repository\BaseRepository`. Inject `ByJG\AnyDataset\Db\DatabaseExecutor` and hand it to `ByJG\MicroOrm\Repository`, pointing at your model class:

```php
<?php

namespace RestReferenceArchitecture\Repository;

use ByJG\AnyDataset\Db\DatabaseExecutor;
use ByJG\MicroOrm\Repository;
use RestReferenceArchitecture\Model\Project;

class ProjectRepository extends BaseRepository
{
    public function __construct(DatabaseExecutor $executor)
    {
        $this->repository = new Repository($executor, Project::class);
    }
}
```

`BaseRepository` already exposes useful helpers:

```php
$project = $repository->get($id);
$list  = $repository->list(page: 0, size: 20);
$model = $repository->model();          // New instance of mapped entity
$repo  = $repository->getRepository();  // Direct access to ByJG\Repository
$mapper = $repository->getMapper();     // Underlying Mapper instance
```

Saving and deleting records delegates to Micro ORM while keeping literals in sync:

```php
$model = $repository->model()
    ->setName('hello world');

$saved = $repository->save($model);
$repository->delete($saved->getId());
```

## 3. Custom Queries

Use `ByJG\MicroOrm\Query` (or any `QueryBuilderInterface`) for filtered results:

```php
use ByJG\MicroOrm\Query;

class ProjectRepository extends BaseRepository
{
    public function findByName(string $value): array
    {
        $query = Query::getInstance()
            ->table($this->getMapper()->getTable())
            ->where('name = :value', ['value' => $value])
            ->orderBy(['id DESC']);

        return $this->getRepository()->getByQuery($query);
    }
}
```

`listQuery()` (in `BaseRepository`) already builds paginated queries—pass filters, order clauses, and selected fields as needed. For raw arrays (instead of model hydration), call `listGeneric()` to run ad-hoc queries while reusing the same pagination helpers.

## 4. Working with UUIDs

Binary UUID columns are transparent when you rely on the attribute helpers:

- `#[TableMySqlUuidPKAttribute("task")]` wires the table and default UUID generator.
- `#[FieldUuidAttribute(primaryKey: true)]` handles binary ⇄ string conversion automatically.
- `HexUuidLiteral::create($uuid)` lets you query by UUID strings without manual hex handling.

`BaseRepository::save()` normalizes any UUID `Literal` back to a formatted string so controllers/tests can keep using human-readable IDs.

## 5. Services and REST Controllers

Repositories are registered in `api/config/<env>/04-repositories.php` and injected into services (see `api/src/Service/ProjectService.php`). Services orchestrate repositories, and REST controllers resolve services via the PSR-11 container. For deeper patterns (DTOs, filters, transactions), read [Repository Patterns](repository-advanced.md) and [Service Layer](services.md).

## 6. ActiveRecord REST Endpoints

When using the ActiveRecord pattern, the controller calls model methods directly — no separate service class is required. The reference implementation is `api/src/Controller/NoteController.php`.

### GET — Fetch by ID

```php title="api/src/Controller/NoteController.php (GET by id)"
#[RequireAuthenticated]
public function getNote(HttpResponse $response, HttpRequest $request): void
{
    $model = Note::get($request->attribute('id'));

    if (is_null($model)) {
        throw new Error404Exception("Note not found");
    }

    $response->write($model);
}
```

### GET — List with Pagination

```php title="api/src/Controller/NoteController.php (list)"
#[RequireAuthenticated]
public function listNote(HttpResponse $response, HttpRequest $request): void
{
    $models = Note::all((int)($request->queryString('page') ?? 0), (int)($request->queryString('size') ?? 50));
    $response->write($models);
}
```

### POST — Create

Use `Note::new($payload)` to hydrate a new instance from an array, then call `save()`:

```php title="api/src/Controller/NoteController.php (create)"
#[RequireRole(User::ROLE_ADMIN)]
#[ValidateRequest]
public function postNote(HttpResponse $response, HttpRequest $request): void
{
    $payload = ValidateRequest::getPayload();

    $model = Note::new($payload);
    $model->save();

    $response->write(["id" => $model->getId()]);
}
```

### PUT — Update

Use `->fill($payload)` to apply changes to an existing instance, then call `save()`:

```php title="api/src/Controller/NoteController.php (update)"
#[RequireRole(User::ROLE_ADMIN)]
#[ValidateRequest]
public function putNote(HttpResponse $response, HttpRequest $request): void
{
    $payload = ValidateRequest::getPayload();

    $model = Note::get($payload['id'] ?? null);

    if (is_null($model)) {
        throw new Error404Exception("Note not found");
    }

    $model->fill($payload);
    $model->save();
}
```

**Key differences from service-based controllers:**

| Service pattern | ActiveRecord pattern |
|---|---|
| `Config::get(ProjectService::class)->create($payload)` | `Note::new($payload)->save()` |
| `Config::get(ProjectService::class)->getOrFail($id)` | `Note::get($id)` |
| `$service->update($payload)` | `$model->fill($payload); $model->save()` |
