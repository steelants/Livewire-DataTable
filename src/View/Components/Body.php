<?php

namespace SteelAnts\DataTable\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Body extends Component
{
    /**
     * @param list<string> $selected Selected row keys - needed to render
     *                               the checked attribute server-side.
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
