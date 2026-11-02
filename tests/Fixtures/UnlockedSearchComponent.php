<?php

namespace Tests\Fixtures;

/**
 * Redeclares searchableColumns without #[Locked] - must be refused at boot.
 */
class UnlockedSearchComponent extends PostTableComponent
{
    public array $searchableColumns = ['title'];
}
