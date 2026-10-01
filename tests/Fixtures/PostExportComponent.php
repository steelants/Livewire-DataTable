<?php

namespace Tests\Fixtures;

use SteelAnts\DataTable\Traits\HasExport;

class PostExportComponent extends PostTableComponent
{
    use HasExport;

    public bool $paginated = true;
    public int $itemsPerPage = 2;

    public function headers(): array
    {
        return ['id' => 'ID', 'title' => 'Title', 'score' => 'Score', 'published' => 'Published'];
    }

    public function exportFilename(): string
    {
        return 'posts-' . $this->itemsPerPage;
    }
}
