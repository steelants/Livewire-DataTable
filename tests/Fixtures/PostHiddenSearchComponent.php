<?php

namespace Tests\Fixtures;

/**
 * Searches a column that is not displayed (score) - declared in code, so it is allowed.
 */
class PostHiddenSearchComponent extends PostTableComponent
{
    public bool $filterable = false;
    public array $searchableColumns = ['title', 'score'];

    public function headers(): array
    {
        return ['id' => 'ID', 'title' => 'Title'];
    }
}
