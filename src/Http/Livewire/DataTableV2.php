<?php

namespace SteelAnts\DataTable\Http\Livewire;

use Exception;
use Livewire\Component;
use Illuminate\Database\Eloquent\Builder;

class DataTableV2 extends Component
{
    protected $queryString = ['sortBy','sortDesc'];
    protected $dataset = [];
    public $sortBy;
    public bool $sortDesc = true;

    public function query(): Builder
    {
    }

    public function dataset(): array
    {
        return [
            [
                "cool1" => "test1",
                "cool2" => "test2",
            ],
        ];
    }

    public function headers(): array
    {
        return array_keys($this->getData()[0]);
    }

    public function footers(): array
    {
        return ["totals", count($this->dataset)];
    }

    public function render()
    {
        return view('datatable::data-table-v2', [
            'dataset' => $this->getData(),
            'headers' => $this->headers(),
            'footers' => $this->footers(),
        ]);
    }

    private function getData(): array
    {
        if ($this->dataset != []) {

        } else if (method_exists($this, "query")) {
            $datasetFromDB = [];
            foreach ($this->query()->get() as $item) {
                $tempRow = [];
                foreach ($item->toArray() as $key => $property) {
                    $tempRow[$key] = (method_exists($this, "getColumn{$key}Data") ? $this->{"getColumn{$key}Data"}($property) : $property);
                }
                $tempRow = (method_exists($this, "getRowData") ? $this->{"getRowData"}($tempRow) : $tempRow);
                $datasetFromDB[] = $tempRow;
            }
            $this->dataset = $datasetFromDB;
        } else {
            $this->dataset = $this->dataset();
        }
        
        return collect($this->dataset)->sortBy($this->sortBy, SORT_REGULAR, $this->sortDesc)->toArray();
    }

    public function getRowData($item)
    {
        return $item;
    }

    public function actions($item)
    {
    }

    public function columns()
    {
    }

    public function rows($item)
    {
    }
}
