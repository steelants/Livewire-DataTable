<?php

namespace SteelAnts\DataTable\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SelectionHeader extends Component
{
    /**
     * @param bool $selectPage        Whether the whole page is selected (derived server-side).
     * @param bool $partiallySelected Whether only part of the page is selected - indeterminate state.
     */
    public function __construct(
        public bool $selectable,
        public bool $selectPage = false,
        public bool $partiallySelected = false,
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
