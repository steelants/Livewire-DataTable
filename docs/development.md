# Development

This guide describes how to develop and test SteelAnts DataTable locally.

## Local Package Development

To develop the package inside a Laravel application, clone the repository into
the application's `packages` directory.


## Clone Repository

Create a packages directory in your Laravel project root:

```bash
mkdir packages
```

Clone the repository:

```bash
git clone https://github.com/steelants/Livewire-DataTable.git ./packages/Livewire-DataTable
```


## Configure Composer Autoload

Add the package namespace to your Laravel application's `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "SteelAnts\\DataTable\\": "packages/Livewire-DataTable/src/"
        }
    }
}
```

After updating `composer.json`, refresh the autoloader:

```bash
composer dump-autoload
```


## Register Service Provider

Add the service provider to:

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


## Testing

For information about running tests see:

[Testing documentation](testing.md)


## Row Types

A DataTable row is either an array (`UseDatabase`, `dataset()`) or an Eloquent
model (`UseDatabaseEloquent`). Anything reading values out of a row must accept
both - use the `array|Model` union and read through `data_get()`, and cover both
shapes with tests. See [Testing](testing.md).


## Docker Build

Docker build and release process is handled by the Gitea server.

Example release flow:

```bash
git checkout master

git pull origin master

git pull origin dev

git tag 2.3.2

git push --tags

git checkout dev
```


## Development Workflow

Recommended workflow:

1. Create a feature branch.
2. Implement changes.
3. Add or update tests.
4. Run the test suite.
5. Merge changes into the development branch.


## Next Steps

Continue with:

- [Usage](usage.md)
- [Configuration](configuration.md)
- [Testing](testing.md)
