# Usage

SteelAnts DataTable allows you to create dynamic data tables using Laravel
Livewire components.

The table can be powered by:

- Eloquent queries
- Custom datasets


## Creating a DataTable Component

Create a Livewire component that extends `DataTableComponent`:

```php
namespace App\Livewire;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use SteelAnts\DataTable\Livewire\DataTableComponent;
use SteelAnts\DataTable\Traits\UseDatabase;

class UserTable extends DataTableComponent
{
    use UseDatabase;

    /**
     * Define data source.
     */
    public function query(): Builder
    {
        return User::query();
    }

    /**
     * Define table headers.
     */
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

Render the DataTable component in your Blade template:

```blade
@livewire('user-table', [], key('data-table'))
```


## Using Without Models

If you do not want to use an Eloquent model, you can provide your own dataset.

Instead of implementing `query()`, implement `dataset()`:

```php
public function dataset(): array
{
    return [
        [
            'id' => 1,
            'name' => 'Name 1',
            'email' => 'E-mail 1',
        ],
        [
            'id' => 2,
            'name' => 'Name 2',
            'email' => 'E-mail 2',
        ],
    ];
}
```


## Actions

You can add custom actions to your rows.

Actions can be:

- Livewire actions
- URL actions


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
            'actionClass' => 'text-danger',
            'iconClass' => 'fas fa-trash',
            'confirm' => 'Are you sure you want to delete this post?',
        ],
        [
            'type' => 'url',
            'url' => route('user.show', [
                'id' => $item['id']
            ]),
            'text' => 'Show',
            'iconClass' => 'fas fa-eye',
        ],
    ];
}
```


## Custom Column Rendering

You can customize the output of individual columns:

```php
public function renderColumnName($value, $row)
{
    return '<b>'.e($value).'</b>';
}
```


## Database Model Output

By default, the package can work with serialized database results.

If you need the original model instance, use:

```php
use SteelAnts\DataTable\Traits\UseDatabaseEloquent;
```

instead of:

```php
use SteelAnts\DataTable\Traits\UseDatabase;
```

This allows you to work directly with the Eloquent model.


## Next Steps

Learn more about available features:

- [Sorting](sorting.md)
- [Filtering](filtering.md)
- [Rendering](rendering.md)
- [Configuration](configuration.md)
