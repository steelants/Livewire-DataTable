<?php

declare(strict_types=1);

namespace SteelAnts\DataTable\Traits;

trait UseLoadOnScroll
{
    public bool $canLoadMore = true;

    /**
     * By how many rows the selection grows on each scroll-to-end trigger.
     * 0 = use the initial itemsPerPage.
     */
    public int $loadMoreStep = 0;

    public function bootUseLoadOnScroll(): void
    {
        $this->paginated = true;

        if ($this->loadMoreStep <= 0) {
            $this->loadMoreStep = max(1, $this->itemsPerPage);
        }
    }

    /**
     * Recalculates whether there is anything left to load. Called from DataTableComponent::getData()
     * after itemsTotal is known, so the "load more" trigger isn't rendered once
     * all rows are already loaded - otherwise x-intersect would fire one request
     * needlessly, just to find out there's nothing left.
     */
    public function refreshLoadMoreState(): void
    {
        $this->canLoadMore = $this->itemsPerPage < $this->itemsTotal;
    }

    public function loadMore(): void
    {
        if (!$this->canLoadMore) {
            return;
        }

        $this->itemsPerPage += max(1, $this->loadMoreStep);

        $this->refreshLoadMoreState();
    }
}
