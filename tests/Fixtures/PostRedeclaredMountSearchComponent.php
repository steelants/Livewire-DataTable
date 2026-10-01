<?php

namespace Tests\Fixtures;

/**
 * Redeclares searchableColumns and changes it in mount() to a hidden column.
 */
class PostRedeclaredMountSearchComponent extends PostTableComponent
{
    public bool $filterable = false;
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
