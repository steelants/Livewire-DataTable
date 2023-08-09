<?php

namespace SteelAnts\DataTable\Http\Livewire;

use Exception;
use Livewire\Component;
use Illuminate\Database\Eloquent\Builder;

class DataTableV2 extends Component
{
    protected $queryString = [];

    public function query() : Builder
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

    public function render()
    {
        return view('datatable::data-table-v2', [
            'dataset' => $this->getData(),
            'headers' => $this->headers(),
        ]);
    }

    private function getData(): array
    {
        if (method_exists($this, "dataset")) {
            return $this->dataset();
        }

        $dataset = $this->query();
        $datasetFromDB = [];
        foreach ($dataset as $item) {
            $tempRow = [];
            foreach ($item as $key => $property) {
                $tempRow[$key] = (method_exists($this, "getColumn{$key}Data") ? $this->{"getColumn{$key}Data"}($property) : $property);
            }
            $tempRow = (method_exists($this, "getRowData") ? $this->{"getRowData"}($tempRow) : $tempRow);
            $datasetFromDB[] = $tempRow->toArray();
        }
        
        return $datasetFromDB;
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
        return view('datatable::data-table', [
            'dataset' => $this->getData(),
            'headers' => $this->headers(),
        ]);
    }

}
