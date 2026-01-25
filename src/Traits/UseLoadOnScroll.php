<?php

namespace SteelAnts\DataTable\Traits;

trait UseLoadOnScroll
{
    public bool $canLoadMore = true;

    public function bootUseLoadOnScroll()
    {
        $this->paginated = true;
    }

	public function loadMore()
	{
		if (!$this->canLoadMore) {
			return;
		}

		if ($this->itemsPerPage >= $this->itemsTotal) {
			$this->canLoadMore = false;
		} else {
			$this->itemsPerPage += 100;
		}
	}
}
