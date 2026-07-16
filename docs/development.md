# Development

## Dev Environment

1. Clone repository to `[LARAVEL-ROOT]/packages/`

You need to be positioned at the root of your Laravel project in your terminal.

```bash
git clone https://github.com/steelants/Livewire-DataTable.git ./packages/Livewire-DataTable
```

2. Modify `composer.json`

```json
"autoload": {
    "psr-4": {
        "SteelAnts\\DataTable\\": "packages/Livewire-DataTable/src/"
    }
}
```

3. Add provider to `bootstrap/providers.php`

```php
return [
    ...
    SteelAnts\DataTable\DataTableServiceProvider::class,
    ...
];
```

---

## Docker Build

Docker build and release process is handled by Gitea server.

```bash
git checkout master

git pull origin master
git pull origin dev

git tag 2.3.2
git push --tags

git checkout dev
```

---

## Testing

The package uses [Pest](https://pestphp.com/) with [Orchestra Testbench](https://packages.tools/testbench) and an in-memory SQLite database.

Install dev dependencies:

```bash
composer install
```

Run all tests:

```bash
./vendor/bin/pest
```

Run only sorting tests:

```bash
./vendor/bin/pest tests/Feature/SortingTest.php
```
