<?php

namespace SteelAnts\DataTable\Http\Livewire;

use Exception;
use Livewire\Component;

class DataTable extends Component
{
    protected $queryString = [];

    private $dataset = [];
    public function setQuery(){

    }

    public function setData() : array {

    }

    private function getData() : array {
        if ($this->dataset != []){
            return $this->dataset;
        }

        if (!method_exists($this, "setData")) {
            $dataset =  collect($this->setData());
        }

        $dataset = $this->setQuety();
        foreach ($dataset as $item) {
            $tempRow = [];
            foreach ($item as $key => $property) {
                $tempRow[$key] = (method_exists($this, "getColumn{$key}Data") ? $this->{"getColumn{$key}Data"}($property) : $property);
            }
            $tempRow = (method_exists($this, "getRowData") ? $this->{"getRowData"}($tempRow) : $tempRow);
            $this->dataset[] = $tempRow->toArray();
        }
    }

    public function getRowData($item){
        return $item;
    }

    public function headers() : array {
        return array_keys($this->getData());
    }

    public function actions($item){

    }

    public function columns(){

    }

    public function rows($item){

    }

    public function render()
    {
        return view('datatable::data-table', [
            'dataset' => $this->getData(),
            'headers' => $this->headers(),
        ]);
    }
}
