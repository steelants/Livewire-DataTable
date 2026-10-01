# CSV Export

The `HasExport` trait adds an export button above the table and downloads the data as CSV.


## Enabling Export

```php
use SteelAnts\DataTable\Livewire\DataTableComponent;
use SteelAnts\DataTable\Traits\HasExport;

class UsersTable extends DataTableComponent
{
    use HasExport;

    public function mount()
    {
        parent::mount();
        $this->setFilename('users');
    }
}
```

The button calls `exportCsv()`. The older `serv()` still works as an alias.


## What Is Exported

- every row matching the current search, header filters and sorting - not only the visible page
- values after `row()` and `columnX()` transformations, like the table shows them
- the header labels from `headers()` as the first line
- only the header line when nothing matches

`renderColumnX()`, `renderRow()` and render casts are not applied - they produce HTML.

The database driver reads the rows in chunks of 1000, so a large table is not loaded into memory at once.


## File Name

Set it with `setFilename()` in `mount()`, or override `exportFilename()`:

```php
public function exportFilename(): string
{
    return 'users-' . now()->format('Y-m-d');
}
```

Do not redeclare the `$filename` property in the component - PHP does not allow a trait
property to be redeclared with a different default value.


## Format

- UTF-8 with a BOM, so Excel detects the encoding
- `;` as the separator, `"` as the enclosure
- `null` becomes an empty cell, booleans `1` / `0`, dates `Y-m-d H:i:s`, backed enums their value, arrays JSON
- text starting with `=`, `+`, `-` or `@` gets an apostrophe in front, so spreadsheet applications
  do not run it as a formula (CSV injection); numbers, including negative ones, are kept as they are


## Next Steps

- [Filtering](filtering.md)
- [Sorting](sorting.md)
- [Configuration](configuration.md)
