<?php

namespace SteelAnts\DataTable\Traits;

use ErrorException;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

trait UseDatabase
{
    // public function query(): Builder
    // {
    //      return Model::where('id','>',0)->limit(100);
    // }

    public function headers(): array
    {
        $keys = $this->query()->getModel()->getFillable();
        return array_combine($keys, $keys);
    }

    public function datasetFromDB($query): array
    {
        $datasetFromDB = [];
        $query = $this->getRelationJoins($query);

        if ($this->searchable && !empty($this->searchValue)) {
            $query->where(function ($q) {
                foreach ($this->searchableColumns as $i => $name) {
                    if ($i == 0) {
                        if (strpos($name, ".") === false) {
                            $q->where($q->getModel()->getTable() . "." . $name, 'LIKE', '%' . $this->searchValue . '%');
                        } else {
                            $names = explode('.', $name);
                            $column = array_pop($names);
                            $q->whereRelation(implode(".", $names), $column, 'LIKE', '%' . $this->searchValue . '%');
                        }
                    } else {
                        if (strpos($name, ".") === false) {
                            $q->orWhere($q->getModel()->getTable() . "." . $name, 'LIKE', '%' . $this->searchValue . '%');
                        } else {
                            $names = explode('.', $name);
                            $column = array_pop($names);
                            $q->orWhereRelation(implode(".", $names), $column, 'LIKE', '%' . $this->searchValue . '%');
                        }
                    }
                }
            });
        }

        if ($this->filterable && !empty($this->headerFilter)) {
            $query->where(function ($q) {
                foreach ($this->headerFilter as $name => $value) {
                    if (is_array($value)) {
                        foreach ($value as $key => $val) {
                            $nameLocal = $name . "." . $key;
                            while(is_array($val)){
                                $firstKey = array_key_first($val);
                                $nameLocal = $nameLocal . "." . $firstKey;
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

        $this->itemsTotal = $query->count();

        if ($this->sortable && !empty($this->sortBy)) {
            $orderByColumn = $this->sortBy;
            if (strpos($orderByColumn, ".") !== false) {
                $orderByColumn = $this->getRelationSortColumn($query, $orderByColumn);
            }

            $query->orderBy($orderByColumn, $this->sortDirection);
        }

        if ($this->paginated != false) {
            $query->limit($this->itemsPerPage);
            if ($this->currentPage > 1) {
                $query = $query->offset($this->itemsPerPage * ($this->currentPage - 1));
            }
        }

        foreach ($query->get() as $item) {

            $tempRow = (method_exists($this, "row") ? $this->{"row"}($item) : $item->toArray());

            foreach ($tempRow as $key => $property) {
                    $method = "column" . ucfirst(Str::camel(str_replace('.', '_', $key)));
                $ModelProperty = str_replace('.', '->', $key);
                $tempRow[$key] = (method_exists($this, $method) ? $this->{$method}($item->$ModelProperty) : $property);
            }

            $datasetFromDB[] = $tempRow;
        }
        return $datasetFromDB;
    }

    private function getFiltersWhere(&$q, $name, $value)
    {
        if (empty($value)) {
            return;
        }
        $type = $this->headerFilters()[$name]['type'];
        if ($type == "text") {
            if (strpos($name, ".") === false) {
                $q->where($q->getModel()->getTable() . "." . $name, 'LIKE', '%' . $value . '%');
            } else {
                $names = explode('.', $name);
                $column = array_pop($names);
                $q->whereRelation(implode(".", $names), $column, 'LIKE', '%' . $value . '%');
            }
        } else if ($type == "select") {
            if (strpos($name, ".") === false) {
                $q->where($q->getModel()->getTable() . "." . $name, '=', $value);
            } else {
                $names = explode('.', $name);
                $column = array_pop($names);
                $q->whereRelation(implode(".", $names), $column, '=', $value);
            }
        } else if ($type == "date" || $type == "time" || $type == "datetime-local") {
            if (strpos($name, ".") === false) {
                if (!empty($value['from'])) {
                    $q->where($q->getModel()->getTable() . "." . $name, '>=', $value['from']);
                }
                if (!empty($value['to'])) {
                    $q->where($q->getModel()->getTable() . "." . $name, '<=', $value['to']);
                }
            } else {
                $names = explode('.', $name);
                $column = array_pop($names);
                $table = implode(".", $names);
                if (!empty($value['from'])) {
                    $q->whereRelation($table, $column, '>=', $value['from']);
                }
                if (!empty($value['to'])) {
                    $q->whereRelation($table, $column, '<=', $value['to']);
                }
            }
        }
    }

    private function getRelationJoins(Builder $query): Builder
    {
        $selects = [$query->getModel()->getTable() . '.*'];
        foreach ($this->getHeader() as $header => $headerName) {
            $relation = null;
            if (strpos($header, ".") === false) {
                continue;
            }

            $model = $query->getModel();
            $connection = explode('.', $header);
            $relationName = array_pop($connection);
            foreach ($connection as $key => $relationProperty) {
                $relationProperty = Str::camel($relationProperty);
                if (empty($relation)) {
                    $usingModel = $model;
                } else {
                    $usingModel = $relation->getModel();
                }

                if (!(method_exists($usingModel, $relationProperty))) {
                    break;
                }

                $relation = $usingModel->$relationProperty();

                if ($relation instanceof BelongsTo) {
                    $relatedTable = $relation->getModel()->getTable();
                    if ($query->getQuery()->joins == null || !array_key_exists($relatedTable,array_column($query->getQuery()->joins, null, 'table') ?? [])) {
                        $query->leftJoin($relatedTable, $relatedTable . '.' . $relation->getOwnerKeyName(), '=', $query->{$key > 0 ? Str::camel($connection[$key-1]) . '()->getModel' : 'getModel'}()->getTable() . '.' . $relation->getForeignKeyName());
                    }
                    if (count($connection) == 1) {
                        $selects[] = $relatedTable . '.' . $relationName . ' AS ' . $header;
                    }
                } else if ($relation instanceof HasOne)  {
                    $relatedTable = $relation->getModel()->getTable();
                    //TODO: FIX OTHER RELATIONS
                }
            }
        }

        return $query->select($selects);
    }

    private function getRelation(QueryBuilder $query)
    {
        $relations = [];

        if ($query->joins != null) {
            return $relations;
        }

        foreach ($this->getHeader() as $header => $headerName) {
            if (strpos($header, ".") === false) {
                continue;
            }

            $relations[] = explode('.', $header)[0];
        }

        return $relations;
    }

    private function getRelationSortColumn(Builder $query, string $column): string
    {
        if (strpos($column, ".") === false) {
            throw new ErrorException($column .  " is not a relation column!");
        }

        $connection = explode('.', $column);
        $relationProperty = $connection[0];
        $relationName = array_pop($connection);
        foreach ($connection as $relationProperty) {
            $relationProperty = Str::camel($relationProperty);
            if (empty($relation)) {
                $relation = $query->getModel()->$relationProperty();
            } else {
                $relation = $relation->getModel()->$relationProperty();
            }
        }
        $relatedTable = $relation->getModel()->getTable();
        return $relatedTable . '.' . $relationName;
    }
}
