<?php

namespace Tests\Fixtures;

use SteelAnts\DataTable\Livewire\DataTableComponent;

/**
 * Array driver counterpart of PostActionsComponent. Rows live in a static
 * property, so delete() survives between Livewire requests.
 */
class ArrayActionsComponent extends DataTableComponent
{
    public static array $rows = [];

    public int $itemsPerPage = 3;

    public function dataset(): array
    {
        return static::$rows;
    }

    public function actions(array $item): array
    {
        return [
            [
                'type'       => 'livewire',
                'action'     => 'delete',
                'parameters' => $item['id'],
                'text'       => 'Delete',
            ],
        ];
    }

    public function delete(int $id): void
    {
        static::$rows = array_values(array_filter(static::$rows, fn ($row) => $row['id'] !== $id));
    }
}
