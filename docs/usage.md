# Usage

```php
namespace App\Livewire;

use App\Models\User;
use SteelAnts\DataTable\Livewire\DataTableComponent;
use Illuminate\Database\Eloquent\Builder;
use SteelAnts\DataTable\Traits\UseDatabase;

class UserTable extends DataTableComponent
{
    Use UseDatabase;
	// or UseDatabaseEloquent, if you want to receive model instead ov serialized array

    // Get model query
    public function query(): Builder
    {
        return User::query();
    }

    // Set headers
    public function headers(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'E-mail',
        ];
    }

    // Set actions
    public function actions($item) : array
    {
        return [
            [
                // livewire action
                'type' => "livewire",
                'action' => "remove",
                'parameters' => $item['id'],
                'text' => "Remove",
                'actionClass' => 'text-danger',
                'iconClass' => 'fas fa-trash',
                'confirm' => 'Are you sure you want to delete this post?',
            ],
            [
                // url action
                'type' => "url",
                'url' => rounte('user.show', [id => $item['id']]),
                'text' => "Show",
                'iconClass' => 'fas fa-eye',
            ]
        ];
    }

    // Custom render of 'name' column
    public function renderColumnName($value, $row){
        return '<b>'.e($value).'</b>';
    }

    // Transform order column on raw order column (optional)
    public function orderColumnName(){
         return 'CAST(name AS STRING)';
    }

    // Livewire actions
    public function remove($id){
        User::find($id)->delete();
    }
}
```

## Using without query / models
```php
    // instead of method query() implement dataset()
    public function dataset(): array
    {
        return [
            [
                'id' => '1',
                'name' => 'Name 1',
                'email' => 'E-mail 1',
            ],
            [
                'id' => '2',
                'name' => 'Name 2',
                'email' => 'E-mail 2',
            ],
            // ...
        ];
    }
```

## Render
```blade
@livewire('user-table', [], key('data-table'))
```
