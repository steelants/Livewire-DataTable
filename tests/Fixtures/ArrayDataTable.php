<?php

namespace Tests\Fixtures;

use SteelAnts\DataTable\Traits\HasBulkActions;

class ArrayDataTable
{
    use HasBulkActions;

    public string $keyPropery = 'id';
    public int $itemsPerPage = 2;
    public int $currentPage = 1;

    public function __construct(private readonly array $rows) {}

    public function currentPageRows(): array
    {
        $from = ($this->currentPage - 1) * $this->itemsPerPage;

        return array_slice($this->rows, $from, $this->itemsPerPage);
    }
}
