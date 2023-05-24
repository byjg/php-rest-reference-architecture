# Database ORM

To query the database you can use the ORM. The ORM uses the [byjg/micro-orm](https://github.com/byjg/micro-orm)

You can start by creating a class inheriting from `BaseRepository` and defining the table name and the primary key.

```php
    public function __construct(DbDriverInterface $dbDriver)
    {
        $mapper = new Mapper(
            Your_Model_Class::class,
            'table_name',
            'primary_key_field'
        );

        $this->repository = new Repository($dbDriver, $mapper);
    }
```

Then you can use the `Repository` class to query the database.

```php
// Get a single row from DB based on your PK and return a model
$repository->get($id)

// Get all records from DB and create them as a list of models
$repository->getAll()

// Delete a row
$repository->delete($model)

// Insert a new row or update an existing row in the database
$repository->save($model)
```

You also can create custom queries:

```php
    public function getByName($value)
    {
        $query = Query::getInstance()
            ->table('table_name')
            ->where('table_name.name = :name', ['name' => $value]);

        $result = $this->repository->getByQuery($query);
        if (is_null($result)) {
            return null;
        }

        return $result;
    }
```
