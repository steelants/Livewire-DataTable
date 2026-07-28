<?php

namespace Tests\Fixtures;

use SteelAnts\DataTable\Traits\HasBulkActions;

class ArrayDataTable
{
    use HasBulkActions;

    public string $keyPropery = 'id';
    public int $itemsPerPage = 2;
    public int $currentPage = 1;

    /** Prepinatelne, aby se dal testovat hook canSelect(). */
    public bool $allowSelection = true;

    /** @var list<array{0:list<string>,1:string}> */
    public array $bulkActionLog = [];

    public function __construct(private readonly array $rows)
    {
        // Livewire vola boot hook traitu pri kazdem requestu - fixture to musi
        // delat taky, jinak by testy bezely v jinem stavu nez realna komponenta.
        $this->bootHasBulkActions();
    }

    public function canSelect(): bool
    {
        return $this->allowSelection;
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
