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
                $tempRow = (method_exists($this, "row") ? $this->{"row"}($item) : $item->toArray());
                foreach ($tempRow as $key => $property) {
                    $tempRow[$key] = (method_exists($this, "colum{$key}Data") ? $this->{"collum{$key}Data"}($property) : $property);
                }
                $datasetFromDB[] = $tempRow;
            }
            $this->dataset = $datasetFromDB;
        } else {
            $this->dataset = $this->dataset();
        }
        
        return collect($this->dataset)->sortBy($this->sortBy, SORT_REGULAR, $this->sortDesc)->toArray();
    }

    public function actions($item)
    {
    }
}
