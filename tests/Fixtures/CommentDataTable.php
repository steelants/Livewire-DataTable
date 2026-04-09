<?php

namespace Tests\Fixtures;

use Illuminate\Contracts\Database\Eloquent\Builder;
use SteelAnts\DataTable\Traits\UseDatabase;

class CommentDataTable
{
    use UseDatabase;

    public bool $sortable = true;
    public array $sortableColumns = [];
    public string $sortBy = '';
    public string $sortDirection = 'asc';
    public bool $paginated = false;
    public int $itemsPerPage = 100;
    public int $currentPage = 1;
    public int $itemsTotal = 0;
    public bool $searchable = false;
    public array $searchableColumns = [];
    public string $searchValue = '';
    public bool $filterable = false;
    public array $headerFilter = [];
    protected array $relationAliases = [];

    public function __construct(
        private readonly \Closure $queryFn,
        private readonly array $headers,
    ) {}

    public function query(): Builder
    {
        return ($this->queryFn)();
    }

    public function getHeader(): array
    {
        return $this->headers;
    }

    public function headerFilters(): array
    {
        return [];
    }
}
