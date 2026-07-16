# Actions

SteelAnts DataTable allows you to define custom actions for each table row.

Actions can execute Livewire methods or redirect users to URLs.


## Defining Actions

Actions are defined using the `actions()` method.

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
