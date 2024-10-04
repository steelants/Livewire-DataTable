# Livewire DataTable
### Created by: [SteelAnts s.r.o.](https://www.steelants.cz/)

[![Total Downloads](https://img.shields.io/packagist/dt/steelants/datatable.svg?style=flat-square)](https://packagist.org/packages/steelants/datatable)

## Usage

```php
namespace App\Livewire;

use App\Models\User;
use SteelAnts\DataTable\Livewire\DataTableComponent;
use Illuminate\Database\Eloquent\Builder;
use SteelAnts\DataTable\Traits\UseDatabase;

class UserTable extends DataTableComponent
{
    Use UseDatabase;
    
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

    // Livewire actions
    public function remove($id){
        User::find($id)->delete();
    }
}
```

### Using without query / models
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

### Render
```blade
@livewire('user-table', [], key('data-table'))
```

### Dev Enviroment
1) Clone Repo to `[LARVEL-ROOT]/packages/`
2) Modify ;composer.json`
```json
    "autoload": {
        "psr-4": {
            ...
            "SteelAnts\\DataTable\\": "packages/Livewire-DataTable/src/"
            ...
        }
    },
```
3) Add (code below) to: `[LARVEL-ROOT]/bootstrap/providers.php`
```php
SteelAnts\DataTable\DataTableServiceProvider::class,
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

## Other Packages
[steelants/laravel-auth](https://github.com/steelants/laravel-auth)

[steelants/laravel-boilerplate](https://github.com/steelants/Laravel-Boilerplate)

[steelants/datatable](https://github.com/steelants/Livewire-DataTable)

[steelants/form](https://github.com/steelants/Laravel-Form)

[steelants/modal](https://github.com/steelants/Livewire-Modal)

[steelants/laravel-tenant](https://github.com/steelants/Laravel-Tenant)

