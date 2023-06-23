<?php

namespace SteelAnts\DataTable\Http\Livewire;

class GenericDataTable extends DataTable
{
    public $contract_id;

    public function mount($model, $properties = [], $headers = [], $items_per_page = 10, $colum_to_search = [], $actions = [], $wheres = [], $contract_id = null)
    {
        $this->actions = [];
        $this->contract_id = $contract_id;
        parent::setModel($model);
        parent::setHeaders($headers);
        parent::setItemPerPage($items_per_page);
        parent::setProperties($properties);
        parent::setSearchColumns($colum_to_search);
        foreach ($actions as $key => $action) {
            parent::addAction( $action['icon'], $action['lang_title'], $action['route'], ($action['is_danger'] ?? false));
        }
        foreach ($wheres as $keyw => $where) {
            parent::setWhere($where['column'], $where['operator'], $where['value']);
        }
        if ($contract_id != null) {
            parent::setWhere("contract_id", "=", $contract_id);
        }
        parent::getData();
    }

    public function rowMutator($item){
        foreach ($item as $key => $property) {
            $item[$key] = (is_array($property) ? implode(', ', $property) : $property);
        }
        return $item;
    }
}
