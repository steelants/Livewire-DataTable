# Livewire DataTable
### Created by: [SteelAnts s.r.o.](https://www.steelants.cz/)

[![Total Downloads](https://img.shields.io/packagist/dt/steelants/datatable.svg?style=flat-square)](https://packagist.org/packages/steelants/datatable)

#### Docker Build
* is handeled by gittea server
```bash
  git checkout master
  git pull origin master
  git pull origin dev
  git tag 2.3.2
  git push --tags
  git checkout dev
```

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
public bool $searchable = true;
public bool $searchableColumns = [];

//Enable filters
public bool $filterable = true;
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

## Filters methods
``` php
    //Add filters to header for specific columns
    public function headerFilters(): array
    {
        return [
            'column1Key' => ['type' => 'text'], //input type
            'column2Key' => ['type' => 'select', 'values' => ['value' => 'name', 'value2' => 'name2']], //this for select
            'column3Key' => ['type' => 'date'], //double input type (date,time,datetime-local)
        ];
    }

    //Add actions to header filters edit
    public function updatedHeaderFilter(){
        $this->validate([
            'headerFilter.column1Key' => 'nullable|string',
            'headerFilter.column2Key' => 'nullable|string',
            'headerFilter.column3Key.*' => 'nullable|date', //have two parameters "from" and "to"
        ]);
    }
```

## Development

1. Create subfolder `/packages` at root of your laravel project

2. clone repository to sub folder `/packages` (you need to be positioned at root of your laravel project in your terminal)
```bash
git clone https://github.com/steelants/Livewire-DataTable.git ./packages/Livewire-DataTable
```

3. edit composer.json file
```json
"autoload": {
	"psr-4": {
		"SteelAnts\\Modal\\": "packages/Livewire-Modal/src/"
	}
}
```

4. Add provider to `bootstrap/providers.php`
```php
return [
	...
     SteelAnts\DataTable\DataTableServiceProvider::class,
	...
];
```

## Contributors
<a href="https://github.com/steelants/Livewire-DataTable/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=steelants/Livewire-DataTable" />
</a>

## Other Packages
[steelants/laravel-auth](https://github.com/steelants/laravel-auth)

[steelants/laravel-boilerplate](https://github.com/steelants/Laravel-Boilerplate)

[steelants/datatable](https://github.com/steelants/Livewire-DataTable)

[steelants/form](https://github.com/steelants/Laravel-Form)

[steelants/modal](https://github.com/steelants/Livewire-Modal)

[steelants/laravel-tenant](https://github.com/steelants/Laravel-Tenant)

