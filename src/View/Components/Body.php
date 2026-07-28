<?php

namespace SteelAnts\DataTable\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Body extends Component
{
    /**
     * @param list<string> $selected Vybrane klice radku - kvuli renderovani
     *                               atributu checked na serveru.
     */
    public function __construct(
        public array $dataset,
        public array $actions,
        public array $headers,
        public array $renderCasts = [],
        public bool $selectable = false,
        public string $keyPropery = 'id',
        public array $selected = [],
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('datatable-components::tbody');
    }
}
