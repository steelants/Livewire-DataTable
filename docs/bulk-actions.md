# Bulk Actions

SteelAnts DataTable allows you to select multiple rows and run an action
against the whole selection at once.


## Enabling Bulk Actions

Add the `HasBulkActions` trait to your DataTable component. This
automatically adds a checkbox column to the table and enables selection —
no other configuration is required.

```php
use SteelAnts\DataTable\Traits\HasBulkActions;

class UserDataTable extends DataTableComponent
{
    use HasBulkActions;

    // ...
}
```

Works with both row shapes: arrays (`UseDatabase`, `dataset()`) and Eloquent
models (`UseDatabaseEloquent`).


## Defining Bulk Actions

Bulk actions are defined using the `bulkActions()` method. The shape mirrors
row [Actions](actions.md).

```php
public function bulkActions(): array
{
    return [
        [
            'type' => 'livewire',
            'action' => 'removeSelected',
            'text' => 'Remove',
            'actionClass' => 'btn-danger',
            'iconClass' => 'fas fa-trash',
            'confirm' => 'Are you sure you want to delete the selected items?',
        ],
    ];
}
```

Return an empty array when the current user is not allowed to run any bulk
action — the bar is then not rendered and `callBulkAction()` has nothing to
dispatch.

Actions are dispatched **by name**, not by their position in the array, so a
conditional `bulkActions()` cannot accidentally trigger the wrong action.
`callBulkAction()` also looks the action up in the current `bulkActions()`
result, which means an action the user cannot see cannot be invoked.


## Processing the Selection

The corresponding Livewire method receives the array of selected keys as its
first argument:

```php
public function removeSelected(array $selected)
{
    // ...
}
```

**Do not query with those keys directly.** `$selected` is a public Livewire
property, so anything can be written into it from the browser. Use
`selectedQuery()` or `eachSelected()` instead — both start from your
component's `query()`, so the selection can never escape its scope:

```php
// Correct - respects the query() scope
$this->selectedQuery()->delete();

// Wrong - ignores the query() scope, accepts forged ids
User::whereIn('id', $selected)->delete();
```


### eachSelected()

For anything with side effects, walk the selection in chunks:

```php
use SteelAnts\DataTable\Support\BulkResult;

public function checkInSelected(array $selected): void
{
    $result = $this->eachSelected(function (Reservation $reservation) {
        if ($reservation->isCheckedIn()) {
            return 'already checked in';   // skipped, grouped by reason
        }

        if (! $reservation->isCheckable()) {
            return false;                  // skipped without a reason
        }

        $reservation->checkIn();

        return true;                       // processed
    });

    $this->clearSelection();

    session()->flash('message', $this->bulkResultMessage($result));
}
```

The callback returns:

| Return | Meaning |
|---|---|
| `true` (or nothing) | processed |
| `false` | skipped |
| `string` | skipped, grouped under that reason |

An exception thrown by the callback marks that single row as failed, reports it
through `report()` and processing continues — one bad row does not abort the
whole action.

`eachSelected()` returns a `BulkResult`:

| Member | Description |
|---|---|
| `$ok` | processed rows |
| `$skipped` | skipped rows |
| `$failed` | rows that threw |
| `$reasons` | `array<string,int>` — skip counts per reason |
| `total()` | `ok + skipped + failed` |
| `isEmpty()` | nothing was processed at all |
| `hasProblems()` | something was skipped or failed |
| `sortedReasons()` | reasons ordered by frequency |

`bulkResultMessage(BulkResult $result)` builds a summary such as
`Processed 8 of 12 items. 4 skipped. already checked in (4)`.


## Selection Behaviour

- A checkbox column is added to the header and every row.
- The header checkbox selects/deselects only the rows on the **current page**. It
  is an action (`toggleSelectPage()`), not a bound property — "is this page
  selected" is derived from the selection and the visible row keys, so it cannot
  drift out of sync. It shows an indeterminate state when only part of the page
  is selected.
- Selection is stored in the `$selected` property and **persists across
  pages** — selecting rows on page 1 and then navigating to page 2 keeps the
  page 1 selection intact. The `checked` attribute is rendered server side, so
  the checkboxes still look selected when you come back to page 1.
- The bulk actions bar (with the selected count and action buttons) is only
  shown once at least one row is selected.
- Selected rows are identified using `$keyPropery` (defaults to `id`), the
  same property used to build row action parameters elsewhere in the
  DataTable component.
- Keys are always kept as strings, because that is what the browser sends.
- Changing a filter, the search value or the sorting **clears the selection**,
  so a bulk action never touches rows the user can no longer see. Switch it off
  with `$clearSelectionOnFilter = false`.


### Selecting Everything

By default only per-page selection is available. To offer "select all
:total", which selects every row matching the current filters even outside the
current page:

```php
public bool $selectAllAcrossPages = true;
```

This requires a `query()` based table. Large selections inflate the Livewire
payload and can hit the database limit on prepared statement parameters (around
65 000 on MySQL), so the total can be capped:

```php
public int $selectAllLimit = 5000;   // 0 = no limit
```

`selectAllFiltered()` returns `false` when the limit would be exceeded, so the
component can inform the user instead of silently selecting a subset.


### Restricting Who Can Select

Override `canSelect()` to hide the checkbox column and refuse every selection
change — useful when the bulk action requires a permission:

```php
public function canSelect(): bool
{
    return auth()->user()->can('check-in');
}
```


## Bulk Action Options

| Option | Description |
|---|---|
| `type` | Action type (`livewire` or `url`), defaults to `livewire` |
| `action` | Livewire method name, also the dispatch key |
| `parameters` | Additional parameters passed after the selection array |
| `url` | URL destination |
| `text` | Display text |
| `iconClass` | Icon CSS class |
| `actionClass` | Additional CSS class |
| `confirm` | Confirmation message |


## Configuration Reference

| Property | Default | Description |
|---|---|---|
| `$selectable` | `true` once the trait boots | Renders the checkbox column |
| `$selected` | `[]` | Selected keys, always `list<string>` |
| `$selectAllAcrossPages` | `false` | Offer "select all" beyond the current page |
| `$selectAllLimit` | `0` | Cap for `selectAllFiltered()`, `0` = no limit |
| `$clearSelectionOnFilter` | `true` | Drop the selection on filter/search/sort change |
| `$bulkChunkSize` | `100` | Chunk size used by `eachSelected()` |


## Methods

| Method | Description |
|---|---|
| `bulkActions()` | Declares the available actions |
| `canSelect()` | Gate for the whole selection feature |
| `selectionEnabled()` | `$selectable && canSelect()` |
| `selectedQuery()` | `query()` restricted to the selection |
| `eachSelected(Closure)` | Walks the selection in chunks, returns `BulkResult` |
| `bulkResultMessage(BulkResult)` | Human readable summary |
| `selectAllFiltered()` | Selects everything matching the current filters |
| `toggleSelectPage()` | Selects or unselects every row on the current page |
| `pageSelected()` | Whether the whole current page is selected |
| `pagePartiallySelected()` | Whether only part of the current page is selected |
| `clearSelection()` | Empties the selection |
| `selectedCount()` | Number of selected rows |
| `isSelected($key)` | Whether a given row key is selected |


## Translations

The bar uses plain `__()` calls with English source strings
(`:count of :total selected`, `Clear selection`, `Select all :total`,
`Processed :ok of :total items.` …). Translate them in your application's
language files — the package ships no translation files of its own.


## Combining With Load On Scroll

With `UseLoadOnScroll` there is only one continuously growing page, so the
header checkbox effectively means "select everything loaded so far", and it
gets unchecked as soon as scrolling brings in new unselected rows. Enabling
`$selectAllAcrossPages` is recommended there, otherwise selecting everything
means scrolling to the very end first.


## Next Steps

Continue with:

- [Actions](actions.md)
- [Configuration](configuration.md)
- [Usage](usage.md)
