# Getting Started - Adding a new field to the Table

Now we have the table `example_crud` created in the [previous tutorial](getting_started_01_create_table.md), 
let's modify it to add a new field `status`.

## Changing the table

We need to add the proper field in the `up` script and remove it in the `down` script.

`db/migrations/up/00003.sql`:

```sql
alter table example_crud
    add status varchar(10) null;
```

`db/migrations/down/00002.sql`:

```sql
alter table example_crud
    drop column status;
```

## Run the migration

```bash
APP_ENV=dev composer run migrate -- update
```


## Adding the field status to the `Model`

Open the file: `src/Model/ExampleCrud.php` and add the field `status`:

```php
...
    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string", nullable: true)]
    protected ?string $status = null;

     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     * @return ExampleCrud
     */
    public function setStatus(?string $status): ExampleCrud
    {
        $this->status = $status;
        return $this;
    }
...
```

## Adding the field status to the `Repository`

As we are just adding a new field, and we already updated the Model to support this new field 
we don't need to change the `Repository` class.

## Adding the field status to the `Rest`

We just need to  allow the rest receive the new field. If we don't do it the API will throw an error.

Open the file: `src/Rest/ExampleCrudRest.php` and add the attribute `status` to method `postExampleCrud()`:

```php
    #[OA\RequestBody(
        description: "The object DummyHex to be created",
        required: true,
        content: new OA\JsonContent(
            required: [ "name" ],
            properties: [
                
                new OA\Property(property: "name", type: "string", format: "string"),
                new OA\Property(property: "birthdate", type: "string", format: "date-time", nullable: true),
                new OA\Property(property: "code", type: "integer", format: "int32", nullable: true),
                new OA\Property(property: "status", type: "string", format: "string", nullable: true)    # <-- Add this line
            ]
        )
    )]
    public function postExampleCrud(HttpResponse $response, HttpRequest $request)
```

## Adding the field status to the `Test`

We only need to change our method `getSample()` to return the status.
Open the file: `tests/Functional/Rest/ExampleCrudTest.php`

```php
    protected function getSampleData($array = false)
    {
        $sample = [

            'name' => 'name',
            'birthdate' => '2023-01-01 00:00:00',
            'code' => 1,
            'status' => 'status',                     # <-- Add this line
        ];
...
```

## Update the OpenAPI

```bash
composer run openapi
```

## Run the tests

If everything is ok, the tests should pass:

```bash
composer run test
```

## Continue the tutorial

[Next: Creating a rest method](getting_started_03_create_rest_method.md)

