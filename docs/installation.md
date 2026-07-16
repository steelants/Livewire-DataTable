# Installation

## Requirements

SteelAnts DataTable requires:

- PHP 8.1+
- Laravel 10+
- Livewire 3+

Make sure your Laravel application is installed and working before installing the package.


## Install via Composer

Install the package using Composer:

```bash
composer require steelants/datatable
```


## Service Provider

The package service provider is registered automatically by Laravel package discovery.

No manual configuration is required.


## Publish Configuration

If you need to customize the package configuration, publish the configuration file:

```bash
php artisan vendor:publish --tag=datatable-config
```

The configuration file will be available at:

```
config/datatable.php
```


## Verify Installation

After installation, you can create your first DataTable component.

Continue with:

[Basic Usage](usage.md)
