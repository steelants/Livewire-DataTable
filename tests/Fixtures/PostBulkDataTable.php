<?php

namespace Tests\Fixtures;

use Illuminate\Contracts\Database\Eloquent\Builder;
use SteelAnts\DataTable\Traits\HasBulkActions;
use SteelAnts\DataTable\Traits\UseDatabaseEloquent;

/**
 * A table over Eloquent - rows are models, not arrays.
 *
 * This is a scenario ArrayDataTable doesn't cover and where HasBulkActions
 * used to break on the array type hint.
 */
class PostBulkDataTable
{
    use HasBulkActions;
    use UseDatabaseEloquent;

    public bool $sortable = false;
    public array $sortableColumns = [];
    public string $sortBy = '';
    public string $sortDirection = 'asc';
    public bool $paginated = true;
    public int $itemsPerPage = 2;
    public int $currentPage = 1;
    public int $itemsTotal = 0;
    public bool $searchable = false;
    public array $searchableColumns = [];
    public string $searchValue = '';
    public bool $filterable = true;
    public array $headerFilter = [];
    public string $keyPropery = 'id';

    protected array $relationAliases = [];

    /** @var list<array{0:list<string>,1:string}> */
    public array $bulkActionLog = [];

    /** Test-toggleable knobs - the trait's config values are methods, not properties. */
    public bool $allowSelectAll = true;

    public int $selectAllCap = 0;

    public bool $clearOnFilter = true;

    public int $chunkSize = 100;

    public function __construct()
    {
        $this->bootHasBulkActions();
    }

    public function selectAllAcrossPages(): bool
    {
        return $this->allowSelectAll;
    }

    public function selectAllLimit(): int
    {
        return $this->selectAllCap;
    }

    public function clearSelectionOnFilter(): bool
    {
        return $this->clearOnFilter;
    }

    public function bulkChunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * Deliberately narrowed query - tests that the selection can't escape it.
     */
    public function query(): Builder
    {
        return Post::query()->where('published', true);
    }

    public function headers(): array
    {
        return ['id' => 'ID', 'title' => 'Title', 'score' => 'Score'];
    }

    public function getHeader(): array
    {
        return $this->headers();
    }

    public function headerFilters(): array
    {
        return [
            'title' => ['type' => 'text'],
            'score' => ['type' => 'text'],
        ];
    }

    public function bulkActions(): array
    {
        return [
            ['type' => 'livewire', 'action' => 'markSelected', 'text' => 'Mark'],
        ];
    }

    public function markSelected(array $selected): void
    {
        $this->bulkActionLog[] = [$selected, 'markSelected'];
    }

    /**
     * Loads the current page the same way as DataTableComponent::getData().
     *
     * @return list<Post>
     */
    public function loadRows(): array
    {
        $rows = $this->datasetFromDB($this->query());

        $this->refreshSelectionState($rows);

        return $rows;
    }
}
