<?php

namespace SteelAnts\DataTable\Http\Livewire;

use Exception;
use Livewire\Component;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DataTableV2 extends Component
{
    /* RUNTIME VARIABLES */
    public $dataset = [];
    public $actions = [];

    public int $pagesTotal = 1;
    public int $currentPage = 1;
    public int $itemsTotal = 1;

    /* SORTING VARIABLES */
    public $sortable = false;
    public $sortBy;
    public bool $sortDesc = true;

    /* PAGINATION */
    public $paginated = false;
    public int $itemsPerPage = 10;

    // public function query(): Builder
    // {
    //      return Model::where('id','>',0)->limit(100);
    // }

    public function dataset(): array
    {
        return [
            [
                "cool1" => "test1",
                "cool2" => "test2",
            ],
        ];
    }

    // public function row($row) : array
    // {
    //     return $row;
    // }

    // public function colum($colum)
    // {
    //     return "value";
    // }

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

    public function updatedCurrentPage()
    {
        $this->getData(true);
    }

    public function queryString(): array
    {
        $queryStrings = [];
        if ($this->paginated == true) {
            $queryStrings[] = 'currentPage';
        }
        if ($this->itemsPerPage != 0) {
            $queryStrings[] = 'itemsPerPage';
        }
        if ($this->sortable != false) {
            $queryStrings[] = 'sortBy';
            if (!empty($this->sortBy)) {
                $queryStrings[] = 'sortDesc';
            }
        }
        return $queryStrings;
    }

    public function render()
    {
        return view('datatable::data-table-v2', [
            'dataset' => $this->getData(),
            'headers' => $this->getHeader(),
            'footers' => $this->footers(),
        ]);
    }

    private function getData($force = false): array
    {
        $this->itemsTotal = 0;
        if ($this->dataset != [] && $force != true) {
            
        } else if (method_exists($this, "query")) {
            $datasetFromDB = [];
            $actions = [];
            $query = $this->query();
            $this->itemsTotal = $query->count();

            if ($this->paginated != false) {
                $query->limit($this->itemsPerPage);
                if ($this->currentPage > 1) {
                    $query = $query->offset($this->itemsPerPage * ($this->currentPage - 1));
                }
            }

            foreach ($query->get() as $item) {
                $tempRow = (method_exists($this, "row") ? $this->{"row"}($item) : $item->toArray());
                $tempRow['id'] = $item->id;
                foreach ($tempRow as $key => $property) {
                    $tempRow[$key] = (method_exists($this, "colum{$key}Data") ? $this->{"collum{$key}Data"}($property) : $property);
                }
                $datasetFromDB[] = $tempRow;

                if(method_exists($this, "actions")){
                    $actions[] = $this->actions($tempRow);
                }
            }
            $this->dataset = $datasetFromDB;
            $this->actions = $actions;
        } else {
            $this->dataset = $this->dataset();
            $this->itemsTotal = $this->dataset->count();
        }

        if ($this->paginated != false && $this->itemsPerPage != 0) {
            $this->pagesTotal = round(ceil($this->itemsTotal / $this->itemsPerPage));
        }

        $finalCollection = collect($this->dataset);
        if ($this->sortable) {
            $finalCollection = $finalCollection->sortBy($this->sortBy, SORT_REGULAR, $this->sortDesc);
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

        // $datasetHeadersCount = count(array_keys($this->dataset[0]));
        // if ($datasetHeadersCount != count($this->headers())) {
        //     if (($datasetHeadersCount - 1) != count($this->headers())) {
        //         throw new Exception("Number of porperties (" . count(array_keys($this->dataset[0])) . "), need to be equal to number of headers (" . count($this->headers()) . ")");
        //     }
        // }

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
}
