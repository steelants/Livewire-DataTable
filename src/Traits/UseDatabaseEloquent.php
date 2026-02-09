<?php

namespace SteelAnts\DataTable\Traits;

use Illuminate\Support\Str;
use SteelAnts\DataTable\Traits\UseDatabase;

trait UseDatabaseEloquent
{
    use UseDatabase;

    public function datasetFromDB($query): array
    {
        $datasetFromDB = [];
        $query = $this->getRelationJoins($query);

        if ($this->searchable && !empty($this->searchValue)) {
            $query->where(function ($q) {
                foreach ($this->searchableColumns as $i => $name) {
                    $this->applyWhere($q, $name, 'LIKE', '%' . str_replace('*', '%', $this->searchValue) . '%', $i === 0 ? 'where' : 'orWhere');
                }
            });
        }

        if ($this->filterable && !empty($this->headerFilter)) {
            $query->where(function ($q) {
                foreach ($this->headerFilter as $name => $value) {
                    if (is_array($value)) {
                        foreach ($value as $key => $val) {
                            $nameLocal = $name . "." . $key;
                            while (is_array($val)) {
                                $firstKey = array_key_first($val);
                                $nameLocal .= "." . $firstKey;
                                $val = $val[$firstKey];
                            }
                            $this->getFiltersWhere($q, $nameLocal, $val);
                        }
                    } else {
                        $this->getFiltersWhere($q, $name, $value);
                    }
                }
            });
        }

        $this->itemsTotal = (clone $query)->count();

        if ($this->sortable && !empty($this->sortBy)) {
            $orderByColumn = $this->sortBy;
            if (strpos($orderByColumn, ".") !== false) {
                $orderByColumn = $this->getRelationSortColumn($query, $orderByColumn);
            }

            $method = "orderColumn" . ucfirst(Str::camel(str_replace('.', '_', $orderByColumn)));
            if (method_exists($this, $method)) {
                $query->orderByRaw($this->{$method}() . " " . strtoupper($this->sortDirection));
            } else {
                $query->orderBy($orderByColumn, $this->sortDirection);
            }
        }

        if ($this->paginated != false) {
            $query->limit($this->itemsPerPage);
            if ($this->currentPage > 1) {
                $query = $query->offset($this->itemsPerPage * ($this->currentPage - 1));
            }
        }

        $columnMethodCache = [];
        $columnPropertyCache = [];
        foreach (array_keys($this->getHeader()) as $header) {
            $method = "column" . ucfirst(Str::camel(str_replace('.', '_', $header)));
            $columnMethodCache[$header] = method_exists($this, $method) ? $method : null;
            $columnPropertyCache[$header] = str_replace('.', '->', $header);
        }

        foreach ($query->get() as $item) {
            $datasetFromDB[] = $item;
        }

        return $datasetFromDB;
    }
}
