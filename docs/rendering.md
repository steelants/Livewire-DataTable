# Rendering

SteelAnts DataTable provides several ways to customize how data is displayed.

You can customize rendering using:

- Render casts
- Row transformations
- Column transformations


## Render Casts

Render casts are the preferred way to customize the output of specific columns.

Define casts using the `renderCasts()` method:

```php
public function renderCasts(): array
{
    return [
        'is_active' => BoolAsIcon::class,
    ];
}
```

Each column can have its own render cast.

The class must implement:

```php
SteelAnts\DataTable\RenderCasts\RenderCast
```


## Creating a Render Cast

Example render cast:

```php
use SteelAnts\DataTable\RenderCasts\RenderCast;

class BoolAsIcon implements RenderCast
{
    public function render($key, $value, $model)
    {
        return '<i class="' .
            ($value
                ? 'far fa-check-circle text-success'
                : 'far fa-times-circle text-danger'
            )
            . '"></i>';
    }
}
```

The `render()` method receives:

| Parameter | Description |
|---|---|
| `$key` | Column key |
| `$value` | Current column value |
| `$model` | Original data model |


## Row Transformation

You can transform the whole row when data is loaded.

The method returns an associative array:

```php
public function row(Model $row): array
{
    return [
        'id' => $row->id,
    ];
}
```


## Column Transformation

You can transform individual columns when data is loaded:

```php
public function columnFoo(mixed $column): mixed
{
    return $column;
}
```

The method name is based on the column name.

Example:

Column:

```php
'name'
```

Method:

```php
columnName()
```


## Render Row Transformation

You can modify the complete row before output.

```php
public function renderRow(array $row): array
{
    return [
        'id' => e($row['id']),
    ];
}
```

> Values are rendered using `{!! !!}`.
> Always escape values manually.


## Render Column Transformation

You can customize individual column output:

```php
public function renderColumnFoo(
    mixed $value,
    array $row
): string
{
    return e($value);
}
```

Example:

Column:

```php
'name'
```

Method:

```php
renderColumnName()
```


## Rendering HTML

Some rendering methods return HTML.

Example:

```php
return '<strong>'.e($value).'</strong>';
```

Make sure values are escaped when inserting user data.


## Next Steps

Continue with:

- [Configuration](configuration.md)
- [Development](development.md)
