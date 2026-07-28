<?php

namespace SteelAnts\DataTable\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SelectionHeader extends Component
{
    public function __construct(
        public bool $selectable,
        public bool $selectPage,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('datatable-components::selection-header');
    }
}
