# Sorting

SteelAnts DataTable supports sorting table columns.

Sorting is enabled by default and can be configured using the DataTable component properties.


## Enable Sorting

To enable sorting explicitly:

```php
public bool $sortable = true;
```

You can also restrict sortable columns:

```php
public array $sortableColumns = [
    'name',
    'score',
    'published',
];
```


## Simple Columns

Sorting by direct database columns works automatically.

Example:

```php
public function headers(): array
{
    return [
        'id' => 'ID',
        'name' => 'Name',
        'score' => 'Score',
        'published' => 'Published',
    ];
}
```

Supported column types:

- string
- integer
- boolean


## BelongsTo Relationships

Relationships can be sorted using dot notation.

Example model relationship:

```php
User belongsTo Company
```

Headers:

```php
public function headers(): array
{
    return [
        'id' => 'ID',
        'name' => 'User',
        'company.name' => 'Company',
    ];
}
```

The package automatically resolves the relationship and creates the required join.

The sorting column:

```php
$sortBy = 'company.name';
```


## HasMany and MorphMany Relationships

The package supports sorting relationships by their count.

Example:

```php
public function headers(): array
{
    return [
        'name' => 'Name',
        'comments.id' => 'Comments',
        'reactions.id' => 'Reactions',
    ];
}
```

The package will sort by the number of related records.

Supported relations:

- HasMany
- MorphMany


Example:

```php
$sortBy = 'comments.id';
```

For morph relations:

```php
$sortBy = 'reactions.id';
```


## Custom Sort Expressions

For advanced sorting requirements, you can override the order method.

Example:

```php
public function orderColumnName(): string
{
    return 'LOWER(name)';
}
```

This allows using custom SQL expressions for ordering.


## Next Steps

Continue with:

- [Filtering](filtering.md)
- [Rendering](rendering.md)
- [Configuration](configuration.md)
