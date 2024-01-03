<?php

namespace SteelAnts\DataTable\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Pagination extends Component
{
    public $startPage = 1;
    public $endPage = 1;
    private $span = 2;
        
    public function __construct(
        public int $currentPage,
        public int $itemsPerPage,
        public int $pagesTotal,
        public int $itemsTotal,
    ) {
        $this->currentPage = max(1, min($this->currentPage, $this->pagesTotal));
        $this->startPage = max(1, $this->currentPage - $this->span);
        $this->endPage = min($this->pagesTotal, $this->currentPage + $this->span);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('datatable-components::pagination');
    }
}
