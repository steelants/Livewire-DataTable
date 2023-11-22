<?php

namespace SteelAnts\DataTable\Http\Livewire;

use Exception;
use Livewire\Component;
use Illuminate\Database\Eloquent\Builder;

class DataTableV2 extends Component
{
    /* RUNTIME VARIABLES */
    protected $dataset = [];

    public int $pagesTotal = 1;
    public int $pagesIndex = 0;

    /* SORTING VARIABLES */
    public $sortable = false;
    public $sortBy;
    public bool $sortDesc = true;

    /* PAGINATION */
    public $paginated = false;
    public int $itemsPerPage = 0;

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
        $this->pagesIndex = 0;
    }

    public function updatedPageIndex()
    {
        $this->getData(true);
    }

    public function queryString(): array
    {
        $queryStrings = [];
        if ($this->paginated == true) {
            $queryStrings[] = 'pagesIndex';
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
        $itemsTotal = 0;
        if ($this->dataset != [] && $force != true) {
        } else if (method_exists($this, "query")) {
            $datasetFromDB = [];
            $query = $this->query();
            $itemsTotal = $query->count();

            if ($this->paginated != false) {
                $query->limit($this->itemsPerPage);
                if ($this->pagesIndex > 0) {
                    $query = $query->offset($this->itemsPerPage * $this->pagesIndex);
                }
            }

            foreach ($query->get() as $item) {
                $tempRow = (method_exists($this, "row") ? $this->{"row"}($item) : $item->toArray());
                $tempRow['id'] = $item->id;
                foreach ($tempRow as $key => $property) {
                    $tempRow[$key] = (method_exists($this, "colum{$key}Data") ? $this->{"collum{$key}Data"}($property) : $property);
                }
                $datasetFromDB[] = $tempRow;
            }
            $this->dataset = $datasetFromDB;
        } else {
            $this->dataset = $this->dataset();
            $itemsTotal = $this->dataset->count();
        }

        if ($this->paginated != false && $this->itemsPerPage != 0) {
            $this->pagesTotal = round($itemsTotal / $this->itemsPerPage);
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

        $datasetHeadersCount = count(array_keys($this->dataset[0]));
        if ($datasetHeadersCount != count($this->headers())) {
            if (($datasetHeadersCount - 1) != count($this->headers())) {
                throw new Exception("Number of porperties (" . count(array_keys($this->dataset[0])) . "), need to be equal to number of headers (" . count($this->headers()) . ")");
            }
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
}
