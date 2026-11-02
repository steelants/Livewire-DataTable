<?php

namespace Tests\Fixtures;

use SteelAnts\DataTable\Traits\HasBulkActions;

class MarkersSelectableComponent extends MarkersActionsComponent
{
    use HasBulkActions;

    public function bulkActions(): array
    {
        return [['type' => 'livewire', 'action' => 'noop', 'text' => 'Noop']];
    }

    public function noop(array $selected): void
    {
    }
}
