<?php

namespace SteelAnts\DataTable\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class HeaderFilters extends Component
{
    public function __construct(
        public array $headers,
        public ?array $headerFilters = null,
        public bool $selectable = false,
        public bool $hasActions = false,
    ) {
    }

    /**
     * Wire model target for the given column filter.
     */
    public function wireModel(string $key): string
    {
        return $this->headerFilters[$key]['wire_model'] ?? 'headerFilter.' . $key;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('datatable-components::header-filters');
    }
}
