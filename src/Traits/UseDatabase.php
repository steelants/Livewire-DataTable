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

    public function datasetFromDB($query): array
    {
        $datasetFromDB = [];
        $query = $this->getRelationJoins($query);

        if ($this->searchable && !empty($this->searchValue)) {
            $query->where(function ($q) {
                foreach ($this->searchableColumns as $i => $column) {
                    if ($i == 0) {
                        if (strpos($column, ".") === false) {
                            $q->where($q->getModel()->getTable() . "." . $column, 'LIKE', '%' . $this->searchValue . '%');
                        } else {
                            $column = explode('.', $column);
                            $q->whereRelation($column[0], $column[1], 'LIKE', '%' . $this->searchValue . '%');
                        }
                    } else {
                        if (strpos($column, ".") === false) {
                            $q->orWhere($q->getModel()->getTable() . "." . $column, 'LIKE', '%' . $this->searchValue . '%');
                        } else {
                            $column = explode('.', $column);
                            $q->orWhereRelation($column[0], $column[1], 'LIKE', '%' . $this->searchValue . '%');
                        }
                    }
                }
            });
        }

        if ($this->filterable && !empty($this->headerFilter)) {
            $query->where(function ($q) {
                foreach ($this->headerFilter as $name => $value) {
                    if (empty($value)) {
                        continue;
                    }
                    $type = $this->headerFilters()[$name]['type'];
                    if ($type == "text") {
                        if (strpos($name, ".") === false) {
                            $q->where($q->getModel()->getTable() . "." . $name, 'LIKE', '%' . $value . '%');
                        } else {
                            $names = explode('.', $name);
                            $q->whereRelation($names[0], $names[1], 'LIKE', '%' . $value . '%');
                        }
                    } else if ($type == "select") {
                        if (strpos($name, ".") === false) {
                            $q->where($q->getModel()->getTable() . "." . $name, '=', $value);
                        } else {
                            $names = explode('.', $name);
                            $q->whereRelation($names[0], $names[1], '=', $value);
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
                            if (!empty($value['from'])) {
                                $q->whereRelation($names[0], $names[1], '>=', $value['from']);
                            }
                            if (!empty($value['to'])) {
                                $q->whereRelation($names[0], $names[1], '<=', $value['to']);
                            }
                        }
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
                $ModelProperty = Str::camel(str_replace('.', '->', $key));

                $tempRow[$key] = (method_exists($this, $method) ? $this->{$method}($item->$ModelProperty) : $property);
            }

            // TODO: do i need this?
            // $tempRow['__key'] = $item->{$this->keyPropery};

            $datasetFromDB[] = $tempRow;
        }
        return $datasetFromDB;
    }

    private function getRelationJoins(Builder $query): Builder
    {
        $selects = [$query->getModel()->getTable() . '.*'];
        foreach ($this->getHeader() as $header => $headerName) {
            if (strpos($header, ".") === false) {
                continue;
            }

            $model = $query->getModel();
            $connection = explode('.', $header);
            $relationProperty = $connection[0];
            $relationName = $connection[1];

            //verify that model has respective Relation
            if (!(method_exists($model, $relationProperty))) {
                continue;
            }


            $relation = $model->$relationProperty();
            if ($relation instanceof BelongsTo) {
                $relatedTable = $relation->getModel()->getTable();
                $query->leftJoin($relatedTable, $relatedTable . '.' . $relation->getOwnerKeyName(), '=', $query->getModel()->getTable() . '.' . $relation->getForeignKeyName());
                $selects[] = $relatedTable . '.' . $relationName . ' AS ' . $header;
            } else if ($relation instanceof HasOne)  {
                $relatedTable = $relation->getModel()->getTable();
                //TODO: FIX OTHER RELATIONS
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
        $relationName = $connection[1];

        $relation = $query->getModel()->$relationProperty();
        $relatedTable = $relation->getModel()->getTable();

        return $relatedTable . '.' . $relationName;
    }
}
