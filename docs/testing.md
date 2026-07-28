# Testing

SteelAnts DataTable uses automated tests to verify package functionality.

The package uses:

- Pest
- Orchestra Testbench
- SQLite in-memory database


## Install Dependencies

Install development dependencies:

```bash
composer install
```


## Running Tests

Run the complete test suite:

```bash
./vendor/bin/pest
```


## Running Specific Tests

You can run only selected tests by providing the test file:

```bash
./vendor/bin/pest tests/Feature/SortingTest.php
```


## Test Environment

Tests run using an in-memory SQLite database.

This allows tests to execute without requiring an external database server.

`Tests\TestCase` registers the Livewire and DataTable service providers, so
tests can also render the package's Blade components, for example:

```php
Blade::render(
    '<x-datatable-selection-cell :selectable="true" :row="$row" key-propery="id" :selected="$selected" />',
    ['row' => ['id' => 7], 'selected' => ['7']]
);
```


## Covering Both Row Shapes

A DataTable row is either an array (`UseDatabase`, `dataset()`) or an Eloquent
model (`UseDatabaseEloquent`). Anything that reads values out of a row has to be
tested against **both**, otherwise a type mismatch only shows up in the
application. The fixtures reflect that:

| Fixture | Row shape |
|---|---|
| `ArrayDataTable` | arrays |
| `PostDataTable` | arrays, database backed |
| `PostBulkDataTable` | Eloquent models, with bulk actions |

Fixtures also call the trait boot hooks (`bootHasBulkActions()`,
`bootUseLoadOnScroll()`) in their constructor, because Livewire calls them on
every request — without that, tests would run against a state a real component
never has.


## Adding New Tests

When adding new functionality:

1. Create a test covering the new behavior.
2. Run the complete test suite.
3. Verify existing functionality is not affected.


Example test structure:

```
tests/
├── Feature/
│   ├── SortingTest.php
│   └── ...
└── Unit/
    └── ...
```


## Before Creating a Pull Request

Before submitting changes:

Run:

```bash
composer install
```

Then:

```bash
./vendor/bin/pest
```

All tests should pass before merging changes.


## Next Steps

Continue with:

- [Development](development.md)
- [Usage](usage.md)
- [Configuration](configuration.md)
