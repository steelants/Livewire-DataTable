<?php

namespace Tests\Fixtures;

use SteelAnts\DataTable\Traits\UseLoadOnScroll;

class ScrollDataTable
{
    use UseLoadOnScroll;

    public bool $paginated = false;

    public function __construct(
        public int $itemsTotal = 0,
        public int $itemsPerPage = 10,
        int $loadMoreStep = 0,
    ) {
        $this->loadMoreStep = $loadMoreStep;

        // Livewire calls the boot hook on every request.
        $this->bootUseLoadOnScroll();
    }
}
