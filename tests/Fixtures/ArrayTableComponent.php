<?php

namespace Tests\Fixtures;

use SteelAnts\DataTable\Livewire\DataTableComponent;
use SteelAnts\DataTable\Traits\HasBulkActions;

/**
 * Array driver component. Rows and call counters are static, so tests can
 * configure the component without going through mount().
 */
class ArrayTableComponent extends DataTableComponent
{
    use HasBulkActions;

    public static array $rows = [];
    public static array $calls = ['dataset' => 0, 'headerFilters' => 0];
    public static bool $allowSelectAll = true;

    public bool $searchable = true;
    public bool $filterable = true;
    public bool $paginated = false;
    public int $itemsPerPage = 100;

    public static function resetFixture(array $rows): void
    {
        static::$rows = $rows;
        static::$calls = ['dataset' => 0, 'headerFilters' => 0];
        static::$allowSelectAll = true;
    }

    public function dataset(): array
    {
        static::$calls['dataset']++;

        return static::$rows;
    }

    public function headerFilters(): array
    {
        static::$calls['headerFilters']++;

        return [
            'name'  => ['type' => 'text'],
            'city'  => ['type' => 'select', 'values' => ['Praha' => 'Praha', 'Brno' => 'Brno']],
            'date'  => ['type' => 'date'],
            'score' => ['type' => 'multiselect', 'values' => [5 => '5', 10 => '10', 20 => '20', 30 => '30']],
        ];
    }

    public function selectAllAcrossPages(): bool
    {
        return static::$allowSelectAll;
    }
}
