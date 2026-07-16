# Installation

SteelAnts DataTable can be installed using Composer.

## Composer Installation

Install the package:

```bash
composer require steelants/datatable
```

Composer will download the package and register it in your Laravel application.


## Service Provider

For newer Laravel versions the package provider is automatically discovered.

If package discovery is disabled, register the provider manually.

Add the provider to:

```
bootstrap/providers.php
```

Example:

```php
return [
    App\Providers\AppServiceProvider::class,

    SteelAnts\DataTable\DataTableServiceProvider::class,
];
```


## Livewire Component

Create a Livewire component that extends:

```php
SteelAnts\DataTable\Livewire\DataTableComponent
```

Example:

```php
namespace App\Livewire;

use SteelAnts\DataTable\Livewire\DataTableComponent;

class UserTable extends DataTableComponent
{
}
```


## Publishing Configuration

Currently the package does not require additional configuration files.

All DataTable behavior is configured directly inside your Livewire component.


## Requirements

The package requires:

- Laravel
- Livewire
- PHP version supported by your Laravel version


## Next Steps

Continue with:

- [Usage](usage.md)
- [Configuration](configuration.md)
- [Development](development.md)
