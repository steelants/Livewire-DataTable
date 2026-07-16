# Filtering

SteelAnts DataTable supports filtering data directly from table headers.

Filters allow users to narrow down displayed records without creating custom
filter forms.


## Enable Filtering

Filtering can be enabled using the DataTable component property:

```php
public bool $filterable = true;
```


## Header Filters

Define filters for specific columns using the `headerFilters()` method.

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

## Available Filter Types

### Text Filter

Creates a text input:

```php
'name' => [
    'type' => 'text',
]
```


### Select Filter

Creates a select input:

```php
'status' => [
    'type' => 'select',
    'values' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],
]
```


### Date Filter

Creates a date filter:

```php
'created_at' => [
    'type' => 'date',
]
```

Date filters support range values:

```php
[
    'from',
    'to',
]
```


## Filter Validation

You can validate filter values when they are updated using:

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


## Example Component

Complete example:

```php
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
```


## Next Steps

Continue with:

- [Rendering](rendering.md)
- [Configuration](configuration.md)
