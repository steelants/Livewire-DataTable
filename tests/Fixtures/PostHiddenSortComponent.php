<?php

namespace Tests\Fixtures;

/**
 * Sorted by default by a column that is not displayed.
 */
class PostHiddenSortComponent extends PostTableComponent
{
    public bool $filterable = false;
    public string $sortBy = 'score';

    public function headers(): array
    {
        return ['id' => 'ID', 'title' => 'Title'];
    }
}
