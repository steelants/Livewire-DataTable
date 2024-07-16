<?php

namespace SteelAnts\DataTable\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Head extends Component
{
    public function __construct(
        public array $headers,
        public bool|null $sortable,
        public string|null $sortBy,
        public string|null  $sortDirection,
        public array $sortableColumns,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('datatable-components::thead');
    }
}
