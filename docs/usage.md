# Usage

SteelAnts DataTable provides a Livewire component for displaying and managing
dynamic data tables.

You can use data from:

- Eloquent models
- Custom datasets


## Creating a DataTable Component

Create a Livewire component extending `DataTableComponent`.

Example:

```php
namespace App\Livewire;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use SteelAnts\DataTable\Livewire\DataTableComponent;
use SteelAnts\DataTable\Traits\UseDatabase;

class UserTable extends DataTableComponent
{
    use UseDatabase;

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


## Rendering the Component

Render the table in your Blade template:

```blade
@livewire('user-table', [], key('data-table'))
```


## Using Eloquent Models

By default, `UseDatabase` loads data from your query and transforms it into
table data.

Example:

```php
use SteelAnts\DataTable\Traits\UseDatabase;

class UserTable extends DataTableComponent
{
    use UseDatabase;

    public function query(): Builder
    {
        return User::query();
    }
}
```


## Using Original Eloquent Models

If you need to work with the original Eloquent model instance instead of
serialized row data, use:

```php
use SteelAnts\DataTable\Traits\UseDatabaseEloquent;
```

Example:

```php
class UserTable extends DataTableComponent
{
    use UseDatabaseEloquent;

    public function query(): Builder
    {
        return User::query();
    }
}
```


## Using Without Models

You can provide your own dataset without using an Eloquent query.

Implement the `dataset()` method:

```php
public function dataset(): array
{
    return [
        [
            'id' => 1,
            'name' => 'Name 1',
            'email' => 'email1@example.com',
        ],
        [
            'id' => 2,
            'name' => 'Name 2',
            'email' => 'email2@example.com',
        ],
    ];
}
```


## Headers

The `headers()` method defines table columns.

Example:

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

The array key represents the data field.

The array value represents the displayed column name.


## Actions

You can add custom actions for each row.

Example:

```php
public function actions($item): array
{
    return [
        [
            'type' => 'livewire',
            'action' => 'remove',
            'parameters' => $item['id'],
            'text' => 'Remove',
        ],
    ];
}
```

For detailed information about actions see:

[Actions documentation](actions.md)


## Custom Column Rendering

You can customize individual column output:

```php
public function renderColumnName($value, $row)
{
    return '<strong>'.e($value).'</strong>';
}
```

For advanced rendering options see:

[Rendering documentation](rendering.md)


## Next Steps

Continue with:

- [Actions](actions.md)
- [Sorting](sorting.md)
- [Filtering](filtering.md)
- [Rendering](rendering.md)
- [Configuration](configuration.md)
