<?php

namespace SteelAnts\DataTable\Livewire;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Livewire\Attributes\On;

class DataTableComponent extends Component
{
    /* RUNTIME VARIABLES */
    public $dataset = [];
    public $actions = [];

    public int $pagesTotal = 1;
    public int $currentPage = 1;
    public int $itemsTotal = 1;

    // Enable sorting
    public bool $sortable = true;
    public array $sortableColumns = [];
    public string $sortBy = '';
    public string $sortDirection = 'asc';

    // Enable pagination
    public bool $paginated = true;
    public int $itemsPerPage = 10;

    // Enable fulltext search
    public bool $searchable = false;
    public array $searchableColumns = [];
    public string $searchValue = '';

    public bool $filterable = false;
    public array $filter = [];
    public array $headerFilter = [];

    // Other config
    public string $tableClass = 'table align-middle';
    public string $viewName = 'datatable::data-table';
    public bool $showHeader = true;

    // TODO: do i need this?
    public string $keyPropery = 'id';

    // public function query(): Builder
    // {
    //      return Model::where('id','>',0)->limit(100);
    // }

    // Transformace whole row on input (optional)
    // Returns associative array
    // public function row(Model $row) : array
    // {
    //     return [
    //         'id' => $row->id,
    //     ];
    // }

    // Transform one column on input (optional)
    // public function columnFoo(mixed $column) : mixed
    // {
    //      return $column;
    // }


    // Transform whole row on output (optional)
    // !!! NOTE: values are rendered with {!! !!}, manually escape values
    // public function renderRow(array $row) : array
    // {
    //     return [
    //         'id' => e($row['id'])
    //     ];
    // }

    // Transform one column on output (optional)
    // !!! NOTE: values are rendered with {!! !!}, manually escape values
    // public function renderColumnFoo(mixed $value, array $row) : string
    // {
    //     return e($value);
    // }



    public function dataset(): array
    {
        return [];
    }

    public function headers(): array
    {
        return array_keys($this->dataset[0]);
    }

    public function footers(): array
    {
        return [];
        // $footer = [];
        // $footer[] = "Count";
        // for ($item=1; $item < count($this->dataset[0]); $item++) {
        //     $footer[] = "";
        // }
        // $footer[] = count($this->dataset);
        // return $footer;
    }

    public function headerFilters(): array 
    {
        return [];
    }

    public function updatedHeaderfilter(){
        
    }

    public function updatedItemsPerPage()
    {
        $this->currentPage = 1;
    }

    // TODO
    // public function updatedCurrentPage()
    // {
    //     $this->getData(true);
    // }

    public function queryString(): array
    {
        $queryStrings = [];
        if ($this->paginated == true) {
            $queryStrings[] = 'currentPage';
        }
        if ($this->searchable == true) {
            $queryStrings[] = 'searchValue';
        }
        if ($this->itemsPerPage != 0) {
            $queryStrings[] = 'itemsPerPage';
        }
        if ($this->sortable != false) {
            $queryStrings[] = 'sortBy';
            if (!empty($this->sortBy)) {
                $queryStrings[] = 'sortDirection';
            }
        }
        return $queryStrings;
    }

    private function getData($force = false): array
    {
        if ($this->sortable == true && $this->sortableColumns == []) {
            $this->sortableColumns = array_keys($this->getHeader());
        }

        if ($this->searchable == true && $this->searchableColumns == []) {
            $this->searchableColumns = array_keys($this->getHeader());
        }

        $this->itemsTotal = 0;

        // TODO
        // if ($this->dataset != [] && $force != true) {

        // } else
        if (method_exists($this, "query")) {
            $relations = [];
            foreach ($this->getHeader() as $header) {
                if (strpos($header, ".") === false) {
                    continue;
                }
                $relations[] = explode('.', $header)[0];
            }

            $datasetFromDB = [];
            $actions = [];
            $query = $this->query();

            $query = $this->getRelationJoins($query);

            if ($this->searchable && !empty($this->searchValue)) {
                $query->where(function ($q) {
                    foreach ($this->searchableColumns as $i => $column) {
                        if ($i == 0) {
                            if (strpos($column, ".") === false) {
                                $q->where($q->getModel()->getTable() . "." . $column, 'LIKE', '%' . $this->searchValue . '%');
                            } else {
                                $column = explode('.', $column);
                                $q->whereRelation($column[0], $column[1], 'LIKE', '%' . $this->searchValue . '%');
                            }
                        } else {
                            if (strpos($column, ".") === false) {
                                $q->orWhere($q->getModel()->getTable() . "." . $column, 'LIKE', '%' . $this->searchValue . '%');
                            } else {
                                $column = explode('.', $column);
                                $q->orWhereRelation($column[0], $column[1], 'LIKE', '%' . $this->searchValue . '%');
                            }
                        }
                    }
                });
            }
            $this->itemsTotal = $query->count();

            if ($this->sortable && !empty($this->sortBy)) {
                $orderByColumn = $this->sortBy;
                if (strpos($orderByColumn, ".") !== false) {
                    $orderByColumn = $this->getRelationSortColumn($query, $orderByColumn);
                }

                $query->orderBy($orderByColumn, $this->sortDirection);
            }

            if ($this->paginated != false) {
                $query->limit($this->itemsPerPage);
                if ($this->currentPage > 1) {
                    $query = $query->offset($this->itemsPerPage * ($this->currentPage - 1));
                }
            }

            foreach ($query->get() as $item) {

                $tempRow = (method_exists($this, "row") ? $this->{"row"}($item) : $item->toArray());

                foreach ($tempRow as $key => $property) {
                     $method = "column" . ucfirst(Str::camel(str_replace('.', '_', $key)));
                    $ModelProperty = Str::camel(str_replace('.', '->', $key));

                    $tempRow[$key] = (method_exists($this, $method) ? $this->{$method}($item->$ModelProperty) : $property);
                }

                // TODO: do i need this?
                // $tempRow['__key'] = $item->{$this->keyPropery};

                $datasetFromDB[] = $tempRow;

                if (method_exists($this, "actions")) {
                    $actions[] = $this->actions($tempRow);
                }
            }
            $this->dataset = $datasetFromDB;
            $this->actions = $actions;
        } else {
            $dataset = $this->dataset();
            $this->itemsTotal = count($dataset);

            if ($this->paginated != false) {
                $from = $this->itemsPerPage * ($this->currentPage - 1);
                $this->dataset = array_slice($dataset, $from,  $this->itemsPerPage);
            }

            $actions = [];

            if (method_exists($this, "actions")) {
                foreach ($this->dataset as $tempRow) {
                    $actions[] = $this->actions($tempRow);
                }
            }

            $this->actions = $actions;
        }

        if ($this->paginated != false && $this->itemsPerPage != 0) {
            $this->pagesTotal = round(ceil($this->itemsTotal / $this->itemsPerPage));
        }

        $finalCollection = collect($this->dataset);
        if ($this->sortable) {
            $finalCollection = $finalCollection->sortBy($this->sortBy, SORT_REGULAR, $this->sortDirection == 'desc');
        }

        if ($this->currentPage > $this->pagesTotal) {
            $this->dispatch('updatedCurrentPage', $this->pagesTotal);
        }

        return $finalCollection->toArray();
    }

    #[On('updatedCurrentPage')]
    public function updatedCurrentPage(int $value){
        $this->currentPage = $value;
    }

    private function getHeader(): array
    {

        if (!method_exists($this, "headers")) {
            return [];
        }

        if ($this->dataset == []) {
            return $this->headers();
        }

        return $this->headers();
    }

    // public function actions($item): array
    // {
    //     return [
    //         [
    //             'type' => "route",
    //             'class' => "danger",
    //             'name' => 'task.show',
    //             'parameters' => [
    //                 'task' => $item['id'],
    //             ],
    //         ],
    //         [
    //             'type' => "livewire",
    //             'action' => 'showModal',
    //             'parameters' => [
    //                 'task' => $item['id'],
    //             ],
    //         ],
    //     ];
    // }

    public function render()
    {
        return view($this->viewName, [
            'dataset' => $this->getData(),
            'headers' => $this->getHeader(),
            'footers' => $this->footers(),
            'headerFilters' => $this->headerFilters(),
        ]);
    }

    private function getRelation(QueryBuilder $query)
    {
        $relations = [];

        if ($query->joins != null) {
            return $relations;
        }

        foreach ($this->getHeader() as $header => $headerName) {
            if (strpos($header, ".") === false) {
                continue;
            }

            $relations[] = explode('.', $header)[0];
        }

        return $relations;
    }

    private function getRelationJoins(Builder $query): Builder
    {
        $selects = [$query->getModel()->getTable() . '.*'];
        foreach ($this->getHeader() as $header => $headerName) {
            if (strpos($header, ".") === false) {
                continue;
            }

            $model = $query->getModel();
            $connection = explode('.', $header);
            $relationProperty = $connection[0];
            $relationName = $connection[1];

            //verify that model has respective Relation
            if (!(method_exists($model, $relationProperty))) {
                continue;
            }


            $relation = $model->$relationProperty();
            if ($relation instanceof BelongsTo) {
                $relatedTable = $relation->getModel()->getTable();
                $query->leftJoin($relatedTable, $relatedTable . '.' . $relation->getOwnerKeyName(), '=', $query->getModel()->getTable() . '.' . $relation->getForeignKeyName());
                $selects[] = $relatedTable . '.' . $relationName . ' AS ' . $header;
            } else if ($relation instanceof HasOne)  {
                $relatedTable = $relation->getModel()->getTable();
                //TODO: FIX OTHER RELATIONS
            }
        }

        return $query->select($selects);
    }

    private function getRelationSortColumn(Builder $query, string $column): string
    {
        if (strpos($column, ".") === false) {
            throw $column .  " is not a relation column!";
        }

        $connection = explode('.', $column);
        $relationProperty = $connection[0];
        $relationName = $connection[1];

        $relation = $query->getModel()->$relationProperty();
        $relatedTable = $relation->getModel()->getTable();

        return $relatedTable . '.' . $relationName;
    }

    public function UpdatedSearchValue(){
        $this->currentPage = 1;
    }
}
