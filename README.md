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

<h1>Livewire-DataTable</h1>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/steelants/datatable.svg?style=flat-square)](https://packagist.org/packages/steelants/datatable) [![Total Downloads](https://img.shields.io/packagist/dt/steelants/datatable.svg?style=flat-square)](https://packagist.org/packages/steelants/datatable)

<p>
A powerful DataTable component for Laravel Livewire with sorting, filtering, and custom rendering.
</p>

<p>
Created by <a href="https://steelants.cz">SteelAnts s.r.o.</a>
</p>

</div>


## Installation

Install the package using Composer:

```bash
composer require steelants/datatable
```

See the full installation guide:

[Installation documentation](docs/installation.md)


## Features

SteelAnts DataTable provides:

- Livewire based data tables
- Eloquent model support
- Custom dataset support
- Sorting
- Filtering
- Searching
- Pagination
- Custom column rendering
- Row actions


## Usage

Create a DataTable component:

```php
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
@livewire('user-table', [], key('data-table'))
```


## Documentation

- [Installation](docs/installation.md)
- [Usage](docs/usage.md)
- [Configuration](docs/configuration.md)
- [Actions](docs/actions.md)
- [Sorting](docs/sorting.md)
- [Filtering](docs/filtering.md)
- [Rendering](docs/rendering.md)
- [Development](docs/development.md)
- [Testing](docs/testing.md)


## Contributors

<a href="https://github.com/steelants/Livewire-DataTable/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=steelants/Livewire-DataTable" />
</a>


## Other Packages

- [steelants/laravel-auth](https://github.com/steelants/laravel-auth)
- [Laravel-Boilerplate.Warehouse](https://github.com/steelants/Laravel-Boilerplate.Warehouse)
- [Laravel-Boilerplate](https://github.com/steelants/Laravel-Boilerplate)
- [Laravel-Form](https://github.com/steelants/Laravel-Form)
- [Livewire-Form](https://github.com/steelants/Livewire-Form)
- [Laravel-General](https://github.com/steelants/Laravel-General)
- [Laravel-Tenant](https://github.com/steelants/Laravel-Tenant)
- [Livewire-Modal](https://github.com/steelants/Livewire-Modal)


## License

This package is open-sourced software licensed under the [Apache License 2.0](LICENSE).

Copyright 2023-2026 SteelAnts s.r.o.
