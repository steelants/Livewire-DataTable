<?php

declare(strict_types=1);

namespace SteelAnts\DataTable\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

class SelectionCell extends Component
{
    public mixed $rowKey;

    public bool $isSelected;

    /**
     * @param array<string,mixed>|Model $row Radek je pole (UseDatabase, dataset())
     *                                       nebo model (UseDatabaseEloquent).
     * @param list<string> $selected
     */
    public function __construct(
        public bool $selectable,
        public array|Model $row,
        public string $keyPropery,
        public array $selected = [],
    ) {
        $value = data_get($row, $keyPropery);

        $this->rowKey = (is_array($value) || is_object($value) || is_bool($value) || $value === '')
            ? null
            : $value;

        // Checked se renderuje na serveru zamerne: po Livewire re-renderu (napr.
        // pri prechodu mezi strankami) neni na cem stav checkboxu obnovit.
        $this->isSelected = $this->rowKey !== null
            && in_array((string) $this->rowKey, $selected, true);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('datatable-components::selection-cell');
    }
}
