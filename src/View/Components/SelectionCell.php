<?php

namespace SteelAnts\DataTable\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SelectionCell extends Component
{
    public function __construct(
        public bool $selectable,
        public array $row,
        public string $keyPropery,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('datatable-components::selection-cell');
    }
}
