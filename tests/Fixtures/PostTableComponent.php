<?php

namespace Tests\Fixtures;

use Illuminate\Contracts\Database\Eloquent\Builder;
use SteelAnts\DataTable\Livewire\DataTableComponent;
use SteelAnts\DataTable\Traits\UseDatabase;

/**
 * Database driver component over the posts table.
 */
class PostTableComponent extends DataTableComponent
{
    use UseDatabase;

    public static array $calls = ['headerFilters' => 0];

    public bool $searchable = true;
    public bool $filterable = true;
    public bool $paginated = false;
    public int $itemsPerPage = 100;

    public function query(): Builder
    {
        return Post::query();
    }

    public function headers(): array
    {
        return ['id' => 'ID', 'title' => 'Title', 'score' => 'Score', 'published_at' => 'Published'];
    }

    public function headerFilters(): array
    {
        static::$calls['headerFilters']++;

        return [
            'title'        => ['type' => 'text'],
            'score'        => ['type' => 'select', 'values' => ['10' => '10', '20' => '20']],
            'published_at' => ['type' => 'date'],
        ];
    }
}
