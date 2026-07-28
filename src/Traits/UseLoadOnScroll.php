<?php

declare(strict_types=1);

namespace SteelAnts\DataTable\Traits;

trait UseLoadOnScroll
{
    public bool $canLoadMore = true;

    /**
     * O kolik radku se navysi vyber pri kazdem doskrolovani na konec.
     * 0 = pouzije se pocatecni itemsPerPage.
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
     * Prepocita, jestli je jeste co nacitat. Vola se z DataTableComponent::getData()
     * po zjisteni itemsTotal, aby se "load more" trigger nevykresloval, kdyz uz
     * jsou nactene vsechny radky - jinak by x-intersect vypalil jeden request
     * zbytecne, jen aby zjistil, ze uz nic neni.
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
