<?php

declare(strict_types=1);

namespace SteelAnts\DataTable\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Lista hromadnych akci nad tabulkou.
 *
 * Samostatna komponenta zamerne: hostitelske aplikace si casto prepisuji hlavni
 * view (datatable::data-table), a bez komponenty by musely markup listy opsat.
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
