<?php

namespace SteelAnts\DataTable\Http\Livewire;

use Exception;
use Livewire\Component;

class DataTable extends Component
{
    protected $queryString = ['order_direction', 'order_by', 'sort_by', 'actual_page', 'search_string'];

    //TABLE
    public $order_by = null;
    public $sort_by = null;
    public $order_direction = 'asc';
    public $totals = [];

    public $headers = [];
    public $actions = [];
    public $dataGetFromDB;
    public $complete = null;

    //DATA QUERY
    public $model;
    public $relations = [];
    public $counts = [];
    private $select = [];
    public $properties2 = [];
    public $scopes = [];
    public $wheres = [];

    //PAGINATION
    public $total_pages;
    public $actual_page = 0;
    public $items_per_page;

    //SEARCH
    public $colum_to_search = [];
    public $search_string = null;

    public function __construct()
    {
        // do nothing
    }

    public function nextPage()
    {
        if ($this->actual_page < ($this->total_pages))
            $this->setPage($this->actual_page + 1);
    }

    public function previousPage()
    {
        if ($this->actual_page > 0)
            $this->setPage($this->actual_page - 1);
    }

    public function setPage($page)
    {
        $this->actual_page = $page;
    }

    public function setItemPerPage($items_count)
    {
        $this->items_per_page = $items_count;
    }

    public function setSearchColumns($colum_to_search)
    {
        $this->colum_to_search = [];
        foreach ($colum_to_search as $column) {
            $this->colum_to_search[] = $column;
        }
    }

    public function setProperties($properties)
    {
        foreach ($properties as $property) {
            if (strpos($property, '.') !== false) {
                if (explode(".", $property)[1] == "count") {
                    $this->properties2[] = str_replace(".", "_", $property);
                    $this->counts[] = explode(".", $property)[0];
                } else {
                    $this->properties2[] = str_replace(".", "->", $property);
                    $this->relations[] = explode(".", $property)[0];
                }
            } else {
                $this->properties2[] = $property;
            }
        }
    }

    public function setModel($model)
    {
        $modelName = ucwords($model);
        if (!class_exists("\\App\\Models\\" . $modelName)) {
            return;
        }

        $this->model = $modelName;
    }

    public function setHeaders($headers)
    {
        $this->headers = [];
        foreach ($headers as $header) {
            $headerName = str_replace("_", " ", ucwords($header));
            $this->headers[] = $headerName;
        }
    }

    public function setScope($scope)
    {
        $scopeName = ucwords($scope);
        if (!method_exists($this->getModel(), "scope{$scopeName}")) {
            return;
        }

        $this->scopes[] = $scope;
    }

    public function setWhere($column, $operator = "=", $value)
    {
        //TODO: validate if column Exists
        $this->wheres[] = [$column, $operator, $value];
    }
    public function searchString()
    {
        $this->actual_page = 0;
    }

    public function updatedItemsPerPage($value)
    {
        $this->items_per_page = $value;
    }

    public function orderBy($column)
    {
        $this->actual_page = 0;
        $this->order_by = $column;
        $this->sort_by = null;

        if ($this->order_direction == 'asc') {
            $this->order_direction = 'desc';
        } else {
            $this->order_direction = 'asc';
        }
    }

    public function sortBy($column)
    {
        $this->actual_page = 0;
        $this->sort_by = $column;
        $this->order_by = null;

        if ($this->order_direction == 'asc') {
            $this->order_direction = 'desc';
        } else {
            $this->order_direction = 'asc';
        }
    }

    public function render()
    {
        $this->getData();
        return view('datatable::data-table');
    }

    public function getData($query = null)
    {
        if ($query == null) {
            $query = $this->getQuery();
        }

        if ($this->relations != []) {
            $query = $query->with($this->relations);
        }

        if ($this->counts != []) {
            $query = $query->withCount($this->counts);
        }

        if ($this->colum_to_search != [] && $this->search_string != null) {
            $query->where(function ($query) {
                foreach ($this->colum_to_search as $column) {
                    if (strpos($column, '.') !== false) {
                        $relationsLink = explode(".", $column);
                        if ($relationsLink[1] == 'count') {
                            $query = $query->orHas($relationsLink[0], '=', $this->search_string);
                        } else {
                            $query = $query->orWhereRelation($relationsLink[0], $relationsLink[1], 'LIKE', "%{$this->search_string}%");
                        }
                    } else {
                        $query = $query->orWhere($column, 'LIKE', "%{$this->search_string}%");
                    }
                }
            });
        }

        if ($this->items_per_page != 0) {
            $this->total_pages = round(($query->count() / $this->items_per_page), 0, PHP_ROUND_HALF_UP);
            $query = $query->limit($this->items_per_page);
            if ($this->actual_page > 0) {
                $query = $query->offset($this->items_per_page * $this->actual_page);
            }
        }

        if ($this->order_by != null) {
            $query = $query->orderBy($this->order_by, $this->order_direction);
        }

        if ($this->select != []) {
            $query = $query->select('id', $this->select);
        }

        if ($this->scopes != []) {
            foreach ($this->scopes as $scope) {
                $query = $query->$scope();
            }
        }

        if ($this->wheres != []) {
            foreach ($this->wheres as $where) {
                $query = $query->where($where[0], $where[1], $where[2]);
            }
        }

        $dataFromDB = $query->get();
        if ($this->sort_by != null) {
            if ($this->order_direction == 'asc') {
                $dataFromDB = $dataFromDB->sortBy($this->sort_by);
            } else {
                $dataFromDB = $dataFromDB->sortByDesc($this->sort_by);
            }
        }

        $this->dataGetFromDB = [];

        foreach ($dataFromDB as $item) {
            $tempObject = [];
            foreach ($this->properties2 as $property) {
                $propertiesSingles = explode("->", $property);
                $tempObject[$property] = $this->resolvePropertyValue($item, $propertiesSingles[0], array_slice($propertiesSingles, 1, count($propertiesSingles) - 1));
            }

            $this->dataGetFromDB[] = $this->getMutatedRow($tempObject);
        }

        $this->dataGetFromDB = $this->addTotal($this->dataGetFromDB);
    }

    public function addAction($icon, $lang_title, $route, $isDanger = false)
    {
        $this->actions[] = [
            'icon' => $icon,
            'lang_title' => __($lang_title),
            'route' => $route,
            'is_danger' => $isDanger ?? false,
        ];
    }

    public function getActions($item)
    {
        $validatedActions = $this->actions;
        if (method_exists($this, "validateActions")) {
            foreach ($validatedActions as $key => $temAction) {
                if (!$this->validateActions($item, $temAction))
                    unset($validatedActions[$key]);
            }
        }

        return $validatedActions;
    }

    private function resolvePropertyValue($item, $property, $properties = [])
    {
        if (isset($property) && $properties == []) {
            return $item->{$property};
        }

        if ($item->{$property} == null) {
            return null;
        }

        return $this->resolvePropertyValue($item->{$property}, $properties[0], array_slice($properties, 1, count($properties) - 1));
    }

    private function getModel()
    {
        return app("\\App\\Models\\" . $this->model);
    }

    public function getQuery()
    {
        return $this->getModel()::query();
    }

    private function getMutatedRow($item)
    {
        if (!method_exists($this, "rowMutator")) {
            return $item;
        }

        $modifiedRow = $this->rowMutator($item);
        if (count($modifiedRow) != count($item)) {
            throw new Exception('Incorrect number of properties returned from row mutation');
        }

        return $modifiedRow;
    }

    public function withTotal($totals)
    {
        $this->totals = $totals;
    }

    public function getTotals()
    {
        return $this->totals;
    }

    private function addTotal($data)
    {
        if ($this->totals == []) {
            return $data;
        }

        if ((count(array_intersect($this->totals, array_keys($data[0])))) ? false : true) {
            return $data;
        }

        $dataTotal = [];
        foreach ($data as $key => $item) {
            foreach ($item as $property => $value) {
                if (!array_key_exists($property, $dataTotal)) {
                    $dataTotal[$property] = ((is_numeric($value) && in_array($property, $this->totals)) ? 0 : "t");
                }

                if (is_numeric($value)&& in_array($property, $this->totals)) {
                    $dataTotal[$property] += $value;
                }
            }
        }
        array_push($data, $dataTotal);
        return $data;
    }
}
