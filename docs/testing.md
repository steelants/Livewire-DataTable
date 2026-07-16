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
