<?php

namespace Tests\Fixtures;

use SteelAnts\DataTable\Traits\HasBulkActions;

class PostBulkTableComponent extends PostTableComponent
{
    use HasBulkActions;

    public function selectAllAcrossPages(): bool
    {
        return true;
    }
}
