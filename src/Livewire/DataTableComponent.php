<?php

namespace SteelAnts\DataTable\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;

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
        $this->itemsTotal = 0;


        // TODO
        // if ($this->dataset != [] && $force != true) {

        // } else
        if (method_exists($this, "query")) {
            $datasetFromDB = [];
            $actions = [];
            $query = $this->query();

            if($this->searchable && !empty($this->searchValue)){
                $query->where(function($q){
                    foreach($this->searchableColumns as $i => $column){
                        if($i == 0){
                            $q->where($column, 'LIKE', '%' . $this->searchValue . '%');
                        }else{
                            $q->orWhere($column, 'LIKE', '%' . $this->searchValue . '%');
                        }
                    }
                });
            }


            $this->itemsTotal = $query->count();

            if($this->sortable && !empty($this->sortBy)){
                $query->orderBy($this->sortBy, $this->sortDirection);
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
                    $method = "column".Str::camel(str_replace('.', '_', $key))."Data";
                    $ModelProperty = Str::camel(str_replace('.', '->', $key));

                    $tempRow[$key] = (method_exists($this, $method) ? $this->{$method}($$ModelProperty) : $property);
                }

                // TODO: do i need this?
                // $tempRow['__key'] = $item->{$this->keyPropery};

                $datasetFromDB[] = $tempRow;

                if(method_exists($this, "actions")){
                    $actions[] = $this->actions($tempRow);
                }
            }
            $this->dataset = $datasetFromDB;
            $this->actions = $actions;
        } else {
            $this->dataset = $this->dataset();
            $actions = [];
            if(method_exists($this, "actions")){
                foreach($this->dataset as $tempRow){
                    $actions[] = $this->actions($tempRow);
                }
            }
            $this->actions = $actions;
            $this->itemsTotal = count($this->dataset);
        }

        if ($this->paginated != false && $this->itemsPerPage != 0) {
            $this->pagesTotal = round(ceil($this->itemsTotal / $this->itemsPerPage));
        }

        $finalCollection = collect($this->dataset);
        if ($this->sortable) {
            // TODO: fix
            $finalCollection = $finalCollection->sortBy($this->sortBy, SORT_REGULAR, $this->sortDirection == 'desc');
        }

        return $finalCollection->toArray();
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
        ]);
    }
}
