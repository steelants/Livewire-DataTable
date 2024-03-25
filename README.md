# Livewire DataTable
### Created by: [SteelAnts s.r.o.](https://www.steelants.cz/)

[![Total Downloads](https://img.shields.io/packagist/dt/steelants/datatable.svg?style=flat-square)](https://packagist.org/packages/steelants/datatable)

## Usage

```php
namespace App\Http\Livewire;

use App\Models\User;
use SteelAnts\DataTable\Http\Livewire\DataTableV3;
use Illuminate\Database\Eloquent\Builder;

class UserDataTable extends DataTableV3
{

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
                // livewrei action
                'type' => "livewire",
                'action' => "remove",
                'parameters' => $item['id'],
                'text' => "Remove",
                'actionClass' => 'text-danger',
                'iconClass' => 'fas fa-trash',
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

    // Livewire actions
    public function remove($id){
        User::find($id)->delete();
    }
}
```

```blade
@livewire('user-data-table', [], key('data-table'))
```

## Configuration
```php
// Enable sorting
public bool $sortable = true;

// Enable pagination
public bool $paginated = true;

// Enable fulltext search
public bool $searchable = false;
public bool $searchableColumns = [];
```

## Optional transforms methods
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
