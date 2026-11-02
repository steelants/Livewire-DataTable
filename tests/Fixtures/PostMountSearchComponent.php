<?php

namespace Tests\Fixtures;

/**
 * Sets a hidden searchable column in mount() without redeclaring the property -
 * the inherited #[Locked] keeps it trusted.
 */
class PostMountSearchComponent extends PostTableComponent
{
    public bool $filterable = false;

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
