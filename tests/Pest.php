<?php

use Tests\TestCase;

uses(TestCase::class)->in('Feature');

/**
 * Runs the same data pipeline as render(), without rendering the blade view.
 */
function tableData(SteelAnts\DataTable\Livewire\DataTableComponent $table): array
{
    return Closure::bind(fn () => $this->getData(), $table, SteelAnts\DataTable\Livewire\DataTableComponent::class)();
}
