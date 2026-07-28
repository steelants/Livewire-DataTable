<?php

declare(strict_types=1);

namespace SteelAnts\DataTable\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * The bulk actions bar above the table.
 *
 * Deliberately a standalone component: host applications often override the main
 * view (datatable::data-table), and without the component they'd have to copy the bar's markup.
 */
class BulkBar extends Component
{
    /**
     * @param list<string> $selected
     * @param list<array<string,mixed>> $bulkActions
     */
    public function __construct(
        public bool $selectable = false,
        public array $selected = [],
        public array $bulkActions = [],
        public bool $selectAllAcrossPages = false,
        public int $itemsTotal = 0,
    ) {
    }

    public function visible(): bool
    {
        return $this->selectable && !empty($this->selected) && !empty($this->bulkActions);
    }

    public function canSelectAll(): bool
    {
        return $this->selectAllAcrossPages && count($this->selected) < $this->itemsTotal;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('datatable-components::bulk-bar');
    }
}
