# Filtering

SteelAnts DataTable provides support for filtering table data directly from the
table header.

Filters are defined per column using the `headerFilters()` method.


## Enable Filtering

Filtering can be enabled or disabled using:

```php
public bool $filterable = true;
```

When enabled, filter inputs are displayed in the table header.


## Defining Header Filters

Use the `headerFilters()` method to define filters for columns.

Example:

```php
public function headerFilters(): array
{
    return [
        'name' => [
            'type' => 'text',
        ],

        'status' => [
            'type' => 'select',
            'values' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
        ],

        'created_at' => [
            'type' => 'date',
        ],
    ];
}
```

The array key must match the column key from `headers()`.


## Text Filter

Text filters create a text input.

Example:

```php
'name' => [
    'type' => 'text',
],
```

This can be used for searching string values.


## Select Filter

Select filters provide predefined values.

Example:

```php
'status' => [
    'type' => 'select',
    'values' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],
],
```

The array key is the stored value.

The array value is the displayed label.


## Date Filter

Date filters support date based filtering.

Example:

```php
'created_at' => [
    'type' => 'date',
],
```

Date filters can contain two values:

- from
- to


## Updating Filter Values

You can validate filter changes using:

```php
public function updatedHeaderFilter()
{
    $this->validate([
        'headerFilter.name' => 'nullable|string',
        'headerFilter.status' => 'nullable|string',
        'headerFilter.created_at.*' => 'nullable|date',
    ]);
}
```

This method is called when header filters are updated.


## Complete Example

Example DataTable with filters:

```php
class UserTable extends DataTableComponent
{
    public bool $filterable = true;

    public function headerFilters(): array
    {
        return [
            'name' => [
                'type' => 'text',
            ],

            'role' => [
                'type' => 'select',
                'values' => [
                    'admin' => 'Administrator',
                    'user' => 'User',
                ],
            ],

            'created_at' => [
                'type' => 'date',
            ],
        ];
    }

    public function updatedHeaderFilter()
    {
        $this->validate([
            'headerFilter.name' => 'nullable|string',
            'headerFilter.role' => 'nullable|string',
            'headerFilter.created_at.*' => 'nullable|date',
        ]);
    }
}
```


## Applying Filters Outside The Table

`UseDatabase` exposes the filtering step on its own:

```php
protected function applyFilters($query)
```

It applies the full text search and every value from `$headerFilter` to the
given query and returns it. `datasetFromDB()` calls it internally, and
[Bulk Actions](bulk-actions.md) uses it in `selectAllFiltered()` to collect the
keys of the whole filtered set without sorting or pagination.

Override it when a table needs a filtering rule the built-in types do not
cover, and call the parent implementation so the standard filters keep working.

Note that `applyFilters()` does **not** add the relation joins that
`datasetFromDB()` sets up, so filters over relations are resolved through
`whereRelation()` / `whereHas()`.


## Next Steps

Continue with:

- [Usage](usage.md)
- [Sorting](sorting.md)
- [Bulk Actions](bulk-actions.md)
- [Rendering](rendering.md)
- [Configuration](configuration.md)
- [Development](development.md)
