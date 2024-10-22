<?php

namespace SteelAnts\DataTable\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
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

    public bool $filterable = false;
    public array $filter = [];
    public array $headerFilter = [];

    // Other config
    public string $tableClass = 'table align-middle';
    public string $viewName = 'datatable::data-table';
    public bool $showHeader = true;

    // TODO: do i need this?
    public string $keyPropery = 'id';

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
        $keys = array_keys($this->dataset()[0]);
        $headers = array_combine($keys, $keys);
        return $headers;
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
        //only select and inputs
        //select - ['table name' => ['type' => 'select', 'values' => ['value' => 'name', ''value2' => 'name2']]]
        //text - ['table name' => ['type' => 'text']
        //datetime - ['table name' => ['type' => 'datetime']]
        //atd....
        return array_fill_keys(array_keys($this->getHeader()), ['type' => 'text']);
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

    private function getDatasetFromArray($dataset): array
    {
        $this->itemsTotal = count($dataset);

        if ($this->paginated != false) {
            $from = $this->itemsPerPage * ($this->currentPage - 1);
            $dataset = array_slice($dataset, $from,  $this->itemsPerPage);
        }

        if (($this->filterable && !empty($this->headerFilter)) || ($this->searchable && !empty($this->searchValue))) {
            foreach ($dataset as $key => $item) {
                foreach ($item as $key2 => $property) {
                    if($this->filterable){
                        if (!empty($this->headerFilter[$key2]) && !str_contains($property, $this->headerFilter[$key2])) {
                            unset($dataset[$key]);
                            break;
                        }
                    }
                    if ($this->searchable) {
                        if (!empty($this->searchValue) && !str_contains($property, $this->searchValue)) {
                            unset($dataset[$key]);
                            break;
                        }
                    }
                }
            }
        }

        if (method_exists($this, "row")) {
            foreach ($dataset as $key => $item) {
                $tempRow = $this->row($item);
                foreach ($tempRow as $key2 => $property) {
                    $method = "column" . ucfirst(Str::camel(str_replace('.', '_', $key2)));
                    if (!method_exists($this, $method)) {
                        continue;
                    }
                    $tempRow[$key2] = $this->{$method}($property);
                }
                $dataset[$key] = $tempRow;
            }
        }

        if ($this->sortable && !empty($this->sortBy)) {
            $dataset = collect($dataset)->sortBy($this->sortBy, SORT_REGULAR, ($this->sortDirection == "desc"))->toArray();
        }
        return $dataset;
    }

    private function getData($force = false): array
    {
        $this->setDefaults();

        $this->itemsTotal = 0;
        if (method_exists($this, "query")) {
            $this->dataset = $this->datasetFromDB($this->query());
        } else {
            $this->dataset = $this->getDatasetFromArray($this->dataset());
        }
        if (method_exists($this, "actions")) {
            foreach ($this->dataset as $tempRow) {
                $this->actions[] = $this->actions($tempRow);
            }
        }

        if ($this->paginated != false && $this->itemsPerPage != 0) {
            $this->pagesTotal = round(ceil($this->itemsTotal / $this->itemsPerPage));
        }

        if ($this->currentPage > $this->pagesTotal) {
            $this->dispatch('updatedCurrentPage', $this->pagesTotal);
        }

        return $this->dataset;
    }

    private function setDefaults()
    {
        $this->actions = [];
        if ($this->sortable == true && $this->sortableColumns == []) {
            $this->sortableColumns = array_keys($this->getHeader());
        }

        if ($this->searchable == true && $this->searchableColumns == []) {
            $this->searchableColumns = array_keys($this->getHeader());
        }
    }

    #[On('updatedCurrentPage')]
    public function updatedCurrentPage(int $value){
        $this->currentPage = $value;
    }

    public function getHeader(): array
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
            'headerFilters' => !empty($this->filterable) ? $this->headerFilters() : null,
        ]);
    }

    public function updatedSearchValue(){
        $this->currentPage = 1;
    }
}
