<?php

namespace Tests\Fixtures;

use SteelAnts\DataTable\Livewire\DataTableComponent;

/**
 * Array driver with render casts and renderColumnX() methods.
 */
class RenderComponent extends DataTableComponent
{
    public static array $rows = [];

    public bool $paginated = false;

    public function dataset(): array
    {
        return static::$rows;
    }

    public function renderCasts(): array
    {
        return ['active' => BoolCast::class, 'name' => BoolCast::class];
    }

    // The render cast on the same column wins.
    public function renderColumnName(mixed $value, array $row): string
    {
        return 'column-method-' . $value;
    }

    public function renderColumnNote(mixed $value, array $row): string
    {
        return '<em data-id="' . e($row['id']) . '">' . e($value) . '</em>';
    }
}
