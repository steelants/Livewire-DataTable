<div align="center">

<a href="https://steelants.cz">
	<picture>	
		<source 
			media="(prefers-color-scheme: dark)" 
			srcset="https://steelants.cz/wp-content/uploads/2026/07/white_3.png">
		<img 
			src="https://steelants.cz/wp-content/themes/wp_steelants_v5/img/logo.png"
			alt="SteelAnts"
			width="180">
	</picture>
</a>

<h1>Build dynamic data tables with Laravel Livewire</h1>

<p>
A powerful and customizable DataTable component for Laravel Livewire.
</p>

<p>
Created by <a href="https://steelants.cz">SteelAnts s.r.o.</a>
</p>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/steelants/datatable.svg?style=flat-square)](https://packagist.org/packages/steelants/datatable)

[![Total Downloads](https://img.shields.io/packagist/dt/steelants/datatable.svg?style=flat-square)](https://packagist.org/packages/steelants/datatable)

</div>


## What It Does

SteelAnts DataTable is a Laravel Livewire component for creating dynamic,
sortable and filterable data tables.

The package allows you to:

- Display Eloquent model data
- Use custom datasets without models
- Sort table columns
- Sort relationships
- Filter data
- Search records
- Paginate results
- Add custom actions
- Customize column rendering
- Transform row and column data


## Installation

Install the package via Composer:

```bash
composer require steelants/datatable
```


## Basic Usage

Create your Livewire DataTable component:

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

Render the component:

```blade
@livewire('user-table')
```


## Documentation

- [Installation](docs/installation.md)
- [Usage](docs/usage.md)
- [Sorting](docs/sorting.md)
- [Filtering](docs/filtering.md)
- [Rendering](docs/rendering.md)
- [Configuration](docs/configuration.md)
- [Development](docs/development.md)


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


## License

MIT License
