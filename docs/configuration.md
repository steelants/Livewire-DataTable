# Configuration

SteelAnts DataTable provides several configuration options that control table
behavior.

Configuration is done directly in your DataTable component by defining public
properties.


## Sorting

Sorting can be enabled or disabled.

Enable sorting:

```php
public bool $sortable = true;
```

You can also restrict which columns are allowed to be sorted:

```php
public array $sortableColumns = [
    'name',
    'email',
];
```

For detailed sorting options see:

[Sorting documentation](sorting.md)


## Pagination

Pagination can be enabled or disabled:

```php
public bool $paginated = true;
```


## Search

Enable full text search:

```php
public bool $searchable = true;
```

You can optionally restrict searchable columns:

```php
public array $searchableColumns = [
    'name',
    'email',
];
```


## Filters

Enable table filters:

```php
public bool $filterable = true;
```


## Complete Example

Example DataTable configuration:

```php
class UserTable extends DataTableComponent
{
    public bool $sortable = true;

    public bool $paginated = true;

    public bool $searchable = true;

    public array $searchableColumns = [
        'name',
        'email',
    ];

    public bool $filterable = true;
}
```


## Next Steps

Continue with:

- [Usage](usage.md)
- [Sorting](sorting.md)
- [Filtering](filtering.md)
- [Rendering](rendering.md)
- [Development](development.md)
