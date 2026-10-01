<?php

namespace Tests\Fixtures;

use SteelAnts\DataTable\Livewire\DataTableComponent;
use SteelAnts\DataTable\Traits\HasExport;

class ArrayExportComponent extends DataTableComponent
{
    use HasExport;

    public static array $rows = [];

    public bool $searchable = true;
    public array $searchableColumns = ['name'];
    public bool $filterable = true;
    public int $itemsPerPage = 2;

    public function mount()
    {
        parent::mount();
        $this->setFilename('people');
    }

    public function dataset(): array
    {
        return static::$rows;
    }

    public function headers(): array
    {
        return ['id' => 'ID', 'name' => 'Name', 'city' => 'City', 'note' => 'Note'];
    }

    public function headerFilters(): array
    {
        return ['city' => ['type' => 'select', 'values' => []]];
    }

    public function row(array $row): array
    {
        return $row + ['note' => null];
    }

    public function columnName(mixed $value): string
    {
        return mb_strtoupper((string) $value);
    }
}
