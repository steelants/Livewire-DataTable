<?php

namespace Tests\Fixtures;

use SteelAnts\DataTable\Traits\HasBulkActions;

class ArrayDataTable
{
    use HasBulkActions;

    public string $keyPropery = 'id';
    public int $itemsPerPage = 2;
    public int $currentPage = 1;

    /** Toggleable, so the canSelect() hook can be tested. */
    public bool $allowSelection = true;

    /** The trait's config values are methods, not properties - the fixture exposes them to tests. */
    public bool $clearOnFilter = true;

    /** @var list<array{0:list<string>,1:string}> */
    public array $bulkActionLog = [];

    public function __construct(private readonly array $rows)
    {
        // Livewire calls the trait's boot hook on every request - the fixture must
        // do the same, otherwise tests would run in a different state than the real component.
        $this->bootHasBulkActions();
    }

    public function canSelect(): bool
    {
        return $this->allowSelection;
    }

    public function clearSelectionOnFilter(): bool
    {
        return $this->clearOnFilter;
    }

    public function currentPageRows(): array
    {
        $from = ($this->currentPage - 1) * $this->itemsPerPage;

        return array_slice($this->rows, $from, $this->itemsPerPage);
    }

    public function dataset(): array
    {
        return $this->rows;
    }

    public function bulkActions(): array
    {
        return [
            ['type' => 'livewire', 'action' => 'markSelected', 'text' => 'Mark'],
            ['type' => 'livewire', 'action' => 'withParameters', 'text' => 'Note', 'parameters' => ['hello']],
            ['type' => 'url', 'url' => '/export', 'text' => 'Export'],
            ['type' => 'livewire', 'action' => 'missingMethod', 'text' => 'Broken'],
        ];
    }

    public function markSelected(array $selected): void
    {
        $this->bulkActionLog[] = [$selected, 'markSelected'];
    }

    public function withParameters(array $selected, string $note): void
    {
        $this->bulkActionLog[] = [$selected, $note];
    }
}
