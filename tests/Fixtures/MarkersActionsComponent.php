<?php

namespace Tests\Fixtures;

/**
 * MarkersTableComponent with two row actions; every fifth row has none.
 */
class MarkersActionsComponent extends MarkersTableComponent
{
    public function actions(array $item): array
    {
        if ($item['id'] % 5 === 0) {
            return [];
        }

        return [
            [
                'type'      => 'url',
                'url'       => '/tasks/' . $item['id'] . '/edit',
                'text'      => 'Edit',
                'iconClass' => 'fas fa-pen',
            ],
            [
                'type'        => 'livewire',
                'action'      => 'remove',
                'parameters'  => $item['id'],
                'text'        => 'Delete',
                'confirm'     => 'Really "delete"?',
                'actionClass' => 'text-danger',
            ],
            [
                'type'       => 'onclick',
                'action'     => 'copyRow',
                'parameters' => ['id' => $item['id']],
                'text'       => 'Copy',
            ],
        ];
    }

    public function remove(int $id): void
    {
        static::$rows = array_values(array_filter(static::$rows, fn ($row) => $row['id'] !== $id));
    }
}
