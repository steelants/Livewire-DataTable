# Render casts
New preferred way to customize render.
```php
// Define cast by header key
public function renderCasts(): array
{
	return [
		'is_active' => BoolAsIcon::class,
	];
}
```

Example render cast
```php
use SteelAnts\DataTable\RenderCasts\RenderCast;

class BoolAsIcon implements RenderCast
{
    public function render($key, $value, $model)
    {
        return '<i class="' . ($value ? 'far fa-check-circle text-success' : 'far fa-times-circle text-danger') . '"></i>';
    }
}
```


# Optional transforms methods
Original render customization.
``` php
// Transformace whole row on input (optional)
// Returns associative array
public function row(Model $row) : array
{
    return [
        'id' => $row->id,
    ];
}

// Transform one column on input (optional)
public function columnFoo(mixed $column) : mixed
{
    return $column;
}


// Transform whole row on output (optional)
// !!! NOTE: values are rendered with {!! !!}, manually escape values
public function renderRow(array $row) : array
{
    return [
        'id' => e($row['id'])
    ];
}

// Transform one column on output (optional)
// !!! NOTE: values are rendered with {!! !!}, manually escape values
public function renderColumnFoo(mixed $value, array $row) : string
{
    return e($value);
}
```
