# Actions

SteelAnts DataTable allows you to define custom actions for each row in your
data table.

Actions can execute Livewire methods or redirect users to URLs.


## Defining Actions

Actions are defined using the `actions()` method in your DataTable component.

Example:

```php
public function actions($item): array
{
    return [
        [
            'type' => 'livewire',
            'action' => 'remove',
            'parameters' => $item['id'],
            'text' => 'Remove',
            'actionClass' => 'text-danger',
            'iconClass' => 'fas fa-trash',
            'confirm' => 'Are you sure you want to delete this post?',
        ],
        [
            'type' => 'url',
            'url' => route('user.show', [
                'id' => $item['id'],
            ]),
            'text' => 'Show',
            'iconClass' => 'fas fa-eye',
        ],
    ];
}
```


## Livewire Actions

Livewire actions call a method directly on your DataTable component.

Example:

```php
[
    'type' => 'livewire',
    'action' => 'remove',
    'parameters' => $item['id'],
]
```

The corresponding Livewire method:

```php
public function remove($id)
{
    User::find($id)->delete();
}
```


## URL Actions

URL actions redirect users to another page.

Example:

```php
[
    'type' => 'url',
    'url' => route('user.show', [
        'id' => $item['id'],
    ]),
    'text' => 'Show',
    'iconClass' => 'fas fa-eye',
]
```


## Action Options

| Option | Description |
|---|---|
| `type` | Action type (`livewire` or `url`) |
| `action` | Livewire method name |
| `parameters` | Parameters passed to Livewire action |
| `url` | URL destination |
| `text` | Display text |
| `iconClass` | Icon CSS class |
| `actionClass` | Additional CSS class |
| `confirm` | Confirmation message |


## Next Steps

Continue with:

- [Usage](usage.md)
- [Rendering](rendering.md)
