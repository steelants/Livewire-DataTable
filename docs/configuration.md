# Configuration

SteelAnts DataTable can be configured directly in your DataTable component.

The following properties control the main table behavior.


## Sorting

Enable or disable sorting:

```php
public bool $sortable = true;
```

You can optionally restrict sortable columns:

```php
public array $sortableColumns = [
    'name',
    'score',
    'published',
];
```


## Pagination

Enable or disable pagination:

```php
public bool $paginated = true;
```


## Searching

Enable or disable full-text search:

```php
public bool $searchable = true;
```

You can optionally define searchable columns:

```php
public array $searchableColumns = [];
```


## Filtering

Enable or disable filters:

```php
public bool $filterable = true;
```


## Example Configuration

Example DataTable component configuration:

```php
class UserTable extends DataTableComponent
{
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
}
```


## Next Steps

Continue with:

- [Development](development.md)
```
