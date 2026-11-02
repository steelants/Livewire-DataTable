<?php

namespace Tests\Fixtures;

use SteelAnts\DataTable\Livewire\DataTableComponent;

class QueryStringComponent extends DataTableComponent
{
    public static array $rows = [];

    public bool $searchable = true;
    public int $itemsPerPage = 2;

    public function dataset(): array
    {
        return static::$rows;
    }
}
