<?php

namespace Tests\Fixtures;

use Illuminate\Contracts\Database\Eloquent\Builder;
use SteelAnts\DataTable\Traits\HasBulkActions;
use SteelAnts\DataTable\Traits\UseDatabaseEloquent;

/**
 * Tabulka nad Eloquentem - radky jsou modely, ne pole.
 *
 * Tohle je scenar, ktery ArrayDataTable nepokryva a ve kterem se drive
 * HasBulkActions rozbil na type hintu array.
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

    /** Prepinatelne knoby pro testy - konfigurace traitu jsou metody, ne properties. */
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
     * Zamerne zuzeny dotaz - testuje se, ze vyber z nej nemuze uniknout.
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
     * Nacte aktualni stranku stejnou cestou jako DataTableComponent::getData().
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
