<?php

namespace Tests\Fixtures;

use Livewire\Attributes\Locked;

/**
 * Redeclares searchableColumns and changes it in mount() to a hidden column.
 */
class PostRedeclaredMountSearchComponent extends PostTableComponent
{
    public bool $filterable = false;
    #[Locked]
    public array $searchableColumns = ['title'];

    public function mount()
    {
        parent::mount();
        $this->searchableColumns = ['score'];
    }

    public function headers(): array
    {
        return ['id' => 'ID', 'title' => 'Title'];
    }
}
