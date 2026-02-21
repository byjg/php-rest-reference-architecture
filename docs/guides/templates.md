---
sidebar_position: 180
title: Template Customization
---

# Code Generator Template Customization

Guide to customizing the code generator templates to match your project's specific needs.

## Table of Contents

- [Overview](#overview)
- [Template Structure](#template-structure)
- [Available Variables](#available-variables)
- [Customizing Templates](#customizing-templates)
- [Creating New Templates](#creating-new-templates)
- [Template Examples](#template-examples)

## Overview

The code generator uses [JinjaPhp](https://github.com/byjg/jinja_php) templates stored in `templates/codegen/`.

**Template Engine**: JinjaPhp (Python Jinja2 syntax for PHP)
**Location**: `templates/codegen/`

### Template Types

| Template                  | Generates           | Pattern                   |
|---------------------------|---------------------|---------------------------|
| `model.php.jinja`         | Model class         | Repository & ActiveRecord |
| `repository.php.jinja`    | Repository class    | Repository only           |
| `service.php.jinja`       | Service class       | Repository only           |
| `rest.php.jinja`          | REST controller     | Repository pattern        |
| `restactiverecord.php.jinja` | REST controller | ActiveRecord pattern      |
| `test.php.jinja`          | Test class          | Both patterns             |

## Template Structure

```
templates/
└── codegen/
    ├── model.php.jinja             # Model class
    ├── repository.php.jinja        # Repository class
    ├── service.php.jinja           # Service class
    ├── rest.php.jinja              # Repository REST controller
    ├── restactiverecord.php.jinja  # ActiveRecord REST controller
    └── test.php.jinja              # Test class
```

## Available Variables

### Common Variables

```jinja
{{ namespace }}              # PHP namespace (e.g., RestReferenceArchitecture\Model)
{{ class_name }}             # Class name (e.g., Product)
{{ table_name }}             # Database table name (e.g., products)
{{ primary_key }}            # Primary key field name (e.g., id)
{{ fields }}                 # Array of field definitions
{{ is_activerecord }}        # Boolean: true if ActiveRecord pattern
```

### Field Variables

Each field in `{{ fields }}` contains:

```jinja
{{ field.name }}             # Field name (e.g., name, price)
{{ field.type }}             # PHP type (string, int, float, bool)
{{ field.db_type }}          # Database type (VARCHAR, INT, DECIMAL, etc.)
{{ field.nullable }}         # Boolean: true if NULL allowed
{{ field.length }}           # Field length (for VARCHAR, etc.)
{{ field.default }}          # Default value
{{ field.isPrimary }}        # Boolean: true if primary key
{{ field.isAutoIncrement }}  # Boolean: true if auto-increment
```

## Customizing Templates

### Example: Adding Custom Header

Edit `templates/codegen/model.php.jinja`:

```jinja
<?php

/**
 * Auto-generated Model
 * Generated: {{ "now"|date("Y-m-d H:i:s") }}
 * Table: {{ table_name }}
 * Do not edit manually!
 */

namespace {{ namespace }};

// Rest of template...
```

### Example: Adding Custom Methods

Add custom methods to `templates/codegen/model.php.jinja`:

```jinja
// ... existing getters/setters ...

{% if not is_activerecord %}
    /**
     * Custom validation method
     */
    public function validate(): bool
    {
        // Add validation logic
        return true;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
        {% for field in fields %}
            '{{ field.name }}' => $this->{{ field.name }},
        {% endfor %}
        ];
    }
{% endif %}
```

### Example: Customizing REST Endpoints

Edit `templates/codegen/rest.php.jinja` to add custom endpoints:

```jinja
// ... existing CRUD methods ...

    /**
     * Search {{ class_name }}
     */
    // At the top of the template file add:
    // use ByJG\\RestServer\\Attributes\\RequireAuthenticated;

    #[OA\Get(
        path: "/{{ table_name }}/search",
        security: [["jwt-token" => []]],
        tags: ["{{ class_name }}"]
    )]
    #[OA\Parameter(
        name: "q",
        in: "query",
        required: true,
        schema: new OA\Schema(type: "string")
    )]
    #[RequireAuthenticated]
    public function search{{ class_name }}(HttpResponse $response, HttpRequest $request): void
    {
        $searchTerm = $request->get('q');
        $service = Config::get({{ class_name }}Service::class);

        // Implement search logic
        $results = $service->search($searchTerm);
        $response->write($results);
    }
```

## Creating New Templates

### Custom Template Example

Create `templates/codegen/dto.php.jinja` for Data Transfer Objects:

```jinja
<?php

namespace {{ namespace }};

/**
 * {{ class_name }} Data Transfer Object
 */
class {{ class_name }}DTO
{
{% for field in fields %}
    public {{ field.type }}{% if field.nullable %}|null{% endif %} ${{ field.name }}{% if field.nullable %} = null{% endif %};
{% endfor %}

    public static function fromModel({{ class_name }} $model): self
    {
        $dto = new self();
{% for field in fields %}
        $dto->{{ field.name }} = $model->get{{ field.name|capitalize }}();
{% endfor %}
        return $dto;
    }

    public function toArray(): array
    {
        return [
{% for field in fields %}
            '{{ field.name }}' => $this->{{ field.name }},
{% endfor %}
        ];
    }
}
```

### Register Custom Template

Modify `builder/Scripts.php` to use your custom template:

```php
protected function generateDTO(string $tableName, array $fields): void
{
    $template = $this->jinja->render('dto.php.jinja', [
        'namespace' => 'RestReferenceArchitecture\\DTO',
        'class_name' => $this->getClassName($tableName),
        'fields' => $fields
    ]);

    $filename = "src/DTO/{$this->getClassName($tableName)}DTO.php";
    file_put_contents($filename, $template);
}
```

## Template Examples

### Conditional Code Based on Field Type

```jinja
{% for field in fields %}
    {% if field.type == 'string' %}
    /**
     * @param {{ field.type }}{% if field.nullable %}|null{% endif %} ${{ field.name }}
     * @return $this
     */
    public function set{{ field.name|capitalize }}({% if field.nullable %}?{% endif %}{{ field.type }} ${{ field.name }}): static
    {
        // Trim strings
        $this->{{ field.name }} = ${{ field.name }} !== null ? trim(${{ field.name }}) : null;
        return $this;
    }
    {% elif field.type == 'int' or field.type == 'float' %}
    /**
     * @param {{ field.type }}{% if field.nullable %}|null{% endif %} ${{ field.name }}
     * @return $this
     */
    public function set{{ field.name|capitalize }}({% if field.nullable %}?{% endif %}{{ field.type }} ${{ field.name }}): static
    {
        // Validate positive numbers
        if (${{ field.name }} !== null && ${{ field.name }} < 0) {
            throw new \InvalidArgumentException('{{ field.name }} must be positive');
        }
        $this->{{ field.name }} = ${{ field.name }};
        return $this;
    }
    {% else %}
    public function set{{ field.name|capitalize }}({% if field.nullable %}?{% endif %}{{ field.type }} ${{ field.name }}): static
    {
        $this->{{ field.name }} = ${{ field.name }};
        return $this;
    }
    {% endif %}
{% endfor %}
```

### Adding Timestamp Traits Based on Fields

```jinja
{% set has_created_at = false %}
{% set has_updated_at = false %}
{% set has_deleted_at = false %}

{% for field in fields %}
    {% if field.name == 'created_at' %}{% set has_created_at = true %}{% endif %}
    {% if field.name == 'updated_at' %}{% set has_updated_at = true %}{% endif %}
    {% if field.name == 'deleted_at' %}{% set has_deleted_at = true %}{% endif %}
{% endfor %}

class {{ class_name }}
{
{% if has_created_at %}
    use OaCreatedAt;
{% endif %}
{% if has_updated_at %}
    use OaUpdatedAt;
{% endif %}
{% if has_deleted_at %}
    use OaDeletedAt;
{% endif %}

    // ... rest of class
}
```

### Custom OpenAPI Documentation

```jinja
    /**
     * Create a new {{ class_name }}
     */
    #[OA\Post(
        path: "/{{ table_name }}",
        summary: "Create new {{ class_name }}",
        description: "Creates a new {{ class_name }} record with the provided data",
        security: [["jwt-token" => []]],
        tags: ["{{ class_name }}"]
    )]
    #[OA\RequestBody(
        description: "{{ class_name }} data",
        required: true,
        content: new OA\JsonContent(
            required: [{% for field in fields %}{% if not field.nullable and not field.isAutoIncrement %}"{{ field.name }}"{% if not loop.last %}, {% endif %}{% endif %}{% endfor %}],
            properties: [
{% for field in fields %}
    {% if not field.isPrimary or not field.isAutoIncrement %}
                new OA\Property(
                    property: "{{ field.name }}",
                    type: "{{ field.type }}",
                    {% if field.db_type == 'VARCHAR' %}format: "string",{% endif %}
                    {% if field.nullable %}nullable: true,{% endif %}
                    description: "The {{ field.name }} field"
                ){% if not loop.last %},{% endif %}
    {% endif %}
{% endfor %}
            ]
        )
    )]
```

## Best Practices

### 1. Keep Templates Maintainable

```jinja
{# Good - Clear and readable #}
{% for field in fields %}
    {% if not field.isPrimary %}
    protected {{ field.type }} ${{ field.name }};
    {% endif %}
{% endfor %}

{# Bad - Complex nested logic #}
{% for field in fields %}{% if not field.isPrimary %}protected {{ field.type }} ${{ field.name }};{% endif %}{% endfor %}
```

### 2. Use Comments in Templates

```jinja
{# Generate getters and setters for all non-primary fields #}
{% for field in fields %}
    {% if not field.isPrimary %}
        {# Getter #}
        public function get{{ field.name|capitalize }}() { }

        {# Setter #}
        public function set{{ field.name|capitalize }}($value) { }
    {% endif %}
{% endfor %}
```

### 3. Maintain Consistent Formatting

```jinja
{# Always use proper indentation #}
class {{ class_name }}
{
    {% for field in fields %}
    protected {{ field.type }} ${{ field.name }};
    {% endfor %}

    {% for field in fields %}
    public function get{{ field.name|capitalize }}()
    {
        return $this->{{ field.name }};
    }
    {% endfor %}
}
```

### 4. Test Template Changes

After modifying templates, test generation:

```bash
# Generate with modified template
composer codegen -- --env=dev --table=test_table all --save

# Review generated code
cat src/Model/TestTable.php
cat src/Rest/TestTableRest.php

# Run tests
composer test
```

## Related Documentation

- [Code Generator Usage](../reference/code-generator.md)
- [ORM Guide](orm.md)
- [REST API Development](rest-controllers.md)
- [Architecture Decisions](../concepts/architecture.md)
