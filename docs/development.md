# Development

This document describes how to develop and test SteelAnts DataTable locally.


## Local Development Setup

If you want to modify the package source code or contribute to the project,
you can install the package locally inside a Laravel application.


## Clone Repository

Create a `packages` directory in the root of your Laravel project.

Example:

```
your-laravel-project/
├── app/
├── bootstrap/
├── packages/
└── composer.json
```

Clone the repository:

```bash
git clone https://github.com/steelants/Livewire-DataTable.git ./packages/Livewire-DataTable
```


## Configure Composer

Add the package namespace to your application's `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "SteelAnts\\DataTable\\": "packages/Livewire-DataTable/src/"
        }
    }
}
```

Refresh Composer autoload files:

```bash
composer dump-autoload
```


## Register Service Provider

Add the package provider to:

```
bootstrap/providers.php
```

Example:

```php
return [
    ...
    SteelAnts\DataTable\DataTableServiceProvider::class,
];
```


# Testing

The package uses Pest with Orchestra Testbench and an in-memory SQLite database.


## Install Dependencies

```bash
composer install
```


## Run Tests

Run all tests:

```bash
./vendor/bin/pest
```


Run only sorting tests:

```bash
./vendor/bin/pest tests/Feature/SortingTest.php
```


# Release Process

Package releases are handled through the SteelAnts Gitea server.


Example release flow:

```bash
git checkout master

git pull origin master
git pull origin dev

git tag 2.3.2
git push --tags

git checkout dev
```
