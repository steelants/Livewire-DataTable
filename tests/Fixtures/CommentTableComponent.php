<?php

namespace Tests\Fixtures;

use Illuminate\Contracts\Database\Eloquent\Builder;
use SteelAnts\DataTable\Livewire\DataTableComponent;
use SteelAnts\DataTable\Traits\UseDatabase;

/**
 * Database driver with a BelongsTo column (post.title) - search and
 * header filters on relation columns.
 */
class CommentTableComponent extends DataTableComponent
{
    use UseDatabase;

    public bool $searchable = true;
    public bool $filterable = true;
    public bool $paginated = false;

    public function query(): Builder
    {
        return Comment::query();
    }

    public function headers(): array
    {
        return ['id' => 'ID', 'body' => 'Body', 'post.title' => 'Post', 'post.score' => 'Score'];
    }

    public function headerFilters(): array
    {
        return [
            'body'       => ['type' => 'text'],
            'post.title' => ['type' => 'text'],
            'post.score' => ['type' => 'select', 'values' => []],
        ];
    }
}
