<?php

namespace SteelAnts\DataTable\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Pagination extends Component
{
    public function __construct(
        public int $pagesTotal,
        public int $itemsPerPage,
    ) {

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('datatable::pagination');
    }
}
