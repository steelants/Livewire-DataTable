<?php

namespace Tests\Fixtures;

use Illuminate\Contracts\Database\Eloquent\Builder;
use SteelAnts\DataTable\Livewire\DataTableComponent;
use SteelAnts\DataTable\Traits\UseDatabase;

/**
 * Database driver with a row "delete" action - used to check that actions
 * always carry the id of their own row, also after a row disappears.
 */
class PostActionsComponent extends DataTableComponent
{
    use UseDatabase;

    public int $itemsPerPage = 3;

    public function query(): Builder
    {
        return Post::query();
    }

    public function headers(): array
    {
        return ['id' => 'ID', 'title' => 'Title'];
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
        Post::whereKey($id)->delete();
    }
}
