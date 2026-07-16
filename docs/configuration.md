# Configuration

SteelAnts DataTable can be configured directly inside your DataTable component.

The package provides configuration options for:

- Sorting
- Pagination
- Search
- Filtering
- Column behavior


## Sorting

Sorting is enabled using:

```php
public bool $sortable = true;
```

By default, all available columns can be sorted.

You can restrict sortable columns:

```php
public array $sortableColumns = [
    'name',
    'email',
];
```


## Custom Sorting

For columns requiring a custom database expression you can override:

```php
public function orderColumnName(): string
{
    return 'LOWER(name)';
}
```

For more information see:

[Sorting documentation](sorting.md)


## Pagination

Enable pagination:

```php
public bool $paginated = true;
```

When enabled, the table automatically displays paginated results.


## Search

Enable full text search:

```php
public bool $searchable = true;
```

You can restrict searchable columns:

```php
public array $searchableColumns = [
    'name',
    'email',
];
```


## Filtering

Enable filters:

```php
public bool $filterable = true;
```

Filters are defined using:

```php
public function headerFilters(): array
{
    return [
        'name' => [
            'type' => 'text',
        ],
        'created_at' => [
            'type' => 'date',
        ],
    ];
}
```

Available filter types depend on the filter configuration.

For more information see:

[Filtering documentation](filtering.md)


## Headers

Columns displayed in the table are defined by:

```php
public function headers(): array
{
    return [
        'id' => 'ID',
        'name' => 'Name',
        'email' => 'E-mail',
    ];
}
```

The array key is the data column.

The array value is the displayed label.


## Render Configuration

Column output can be customized using:

```php
public function renderCasts(): array
{
    return [
        'is_active' => BoolAsIcon::class,
    ];
}
```

For more information see:

[Rendering documentation](rendering.md)


## Complete Example

Example DataTable configuration:

```php
class UserTable extends DataTableComponent
{
    use UseDatabase;

    public bool $sortable = true;

    public bool $paginated = true;

    public bool $searchable = true;

    public bool $filterable = true;

    public array $sortableColumns = [
        'name',
        'email',
    ];

    public array $searchableColumns = [
        'name',
        'email',
    ];

    public function query(): Builder
    {
        return User::query();
    }

    public function headers(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'E-mail',
        ];
    }
}
```


## Next Steps

Continue with:

- [Usage](usage.md)
- [Sorting](sorting.md)
- [Filtering](filtering.md)
- [Rendering](rendering.md)
- [Development](development.md)
