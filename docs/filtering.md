# Sorting

SteelAnts DataTable provides built-in sorting support for table columns.

Sorting works with:

- Simple database columns
- BelongsTo relationships
- HasMany and MorphMany relationships
- Custom SQL expressions


## Enable Sorting

Sorting is enabled by default.

You can explicitly enable or disable sorting:

```php
public bool $sortable = true;
```

Disable sorting:

```php
public bool $sortable = false;
```


## Restrict Sortable Columns

By default, all available columns can be sorted.

You can limit sortable columns:

```php
public array $sortableColumns = [
    'name',
    'email',
    'created_at',
];
```

Only columns listed in `$sortableColumns` will be sortable.


## Simple Columns

Sorting works automatically with direct database columns.

Example:

```php
public array $sortableColumns = [
    'name',
    'score',
    'published',
];
```

Supported values include:

- strings
- integers
- booleans
- other standard database column types


## Sorting BelongsTo Relationships

Relationships can be sorted using dot notation.

Example model relationship:

```php
public function user()
{
    return $this->belongsTo(User::class);
}
```

Header definition:

```php
'user.name' => 'User',
```

Sort column:

```php
$sortBy = 'user.name';
```

The package automatically creates the required join.


## Sorting HasMany Relationships

HasMany relationships are sorted by related record count.

Example:

```php
'comments.id' => 'Comments',
```

The package detects the relationship and generates a count query.

Example:

```php
$sortBy = 'comments.id';
```

This sorts rows by the number of related comments.


## Sorting MorphMany Relationships

MorphMany relationships are supported using the same dot notation.

Example:

```php
'reactions.id' => 'Reactions',
```

The package creates the required count query while respecting the morph relationship.


## Custom Sorting Expression

For special cases, you can define a custom sorting expression.

Override:

```php
public function orderColumnName(): string
{
    return 'LOWER(name)';
}
```

The returned value is used as the raw SQL order expression.


## Example DataTable

Example:

```php
class UserTable extends DataTableComponent
{
    use UseDatabase;

    public bool $sortable = true;

    public array $sortableColumns = [
        'name',
        'email',
        'comments.id',
    ];

    public function query(): Builder
    {
        return User::query();
    }

    public function headers(): array
    {
        return [
            'name' => 'Name',
            'email' => 'Email',
            'comments.id' => 'Comments',
        ];
    }
}
```


## Next Steps

Continue with:

- [Usage](usage.md)
- [Filtering](filtering.md)
- [Rendering](rendering.md)
- [Configuration](configuration.md)
- [Development](development.md)
