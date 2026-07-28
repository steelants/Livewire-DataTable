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
            'actionClass' => 'text-danger',
            'iconClass' => 'fas fa-trash',
            'confirm' => 'Are you sure you want to delete the selected items?',
        ],
    ];
}
```

The corresponding Livewire method receives the array of selected keys as its
first argument:

```php
public function removeSelected(array $selected)
{
    User::whereIn('id', $selected)->delete();
}
```


## Selection Behaviour

- A checkbox column is added to the header and every row.
- The header checkbox selects/deselects only the rows on the **current
  page**.
- Selection is stored in the `$selected` property and **persists across
  pages** — selecting rows on page 1 and then navigating to page 2 keeps the
  page 1 selection intact.
- The bulk actions bar (with the selected count and action buttons) is only
  shown once at least one row is selected.
- Selected rows are identified using `$keyPropery` (defaults to `id`), the
  same property used to build row action parameters elsewhere in the
  DataTable component.


## Bulk Action Options

| Option | Description |
|---|---|
| `type` | Action type (`livewire` or `url`) |
| `action` | Livewire method name |
| `parameters` | Additional parameters passed after the selection array |
| `url` | URL destination |
| `text` | Display text |
| `iconClass` | Icon CSS class |
| `actionClass` | Additional CSS class |
| `confirm` | Confirmation message |


## Next Steps

Continue with:

- [Actions](actions.md)
- [Usage](usage.md)
