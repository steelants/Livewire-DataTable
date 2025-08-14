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
    /**
     * Mapping of relation paths to their join aliases.
     *
     * @var array<string,string>
     */
    protected array $relationAliases = [];
    // public function query(): Builder
    // {
    //      return Model::where('id','>',0)->limit(100);
    // }

    // Transform order column on raw order column (optional)
    // public function orderColumnFoo() : mixed
    // {
    //      return 'CAST(string_id AS INTEGER)';
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
                    $this->applyWhere($q, $name, 'LIKE', '%' . $this->searchValue . '%', $i === 0 ? 'where' : 'orWhere');
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
            $tempRow = (method_exists($this, "row") ? $this->{"row"}($item) : $item->toArray());

            foreach ($tempRow as $key => $property) {
                $method = $columnMethodCache[$key] ?? null;
                $modelProperty = $columnPropertyCache[$key] ?? str_replace('.', '->', $key);
                $tempRow[$key] = $method ? $this->{$method}($item->$modelProperty) : $property;
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
        if ($type === "text") {
            $this->applyWhere($q, $name, 'LIKE', '%' . $value . '%');
        } elseif ($type === "select") {
            $this->applyWhere($q, $name, '=', $value);
        } elseif (in_array($type, ["date", "time", "datetime-local"], true)) {
            if (!empty($value['from'])) {
                $this->applyWhere($q, $name, '>=', $value['from']);
            }
            if (!empty($value['to'])) {
                $this->applyWhere($q, $name, '<=', $value['to']);
            }
        }
    }

    private function applyWhere($q, string $name, string $operator, $value, string $boolean = 'where'): void
    {
        $method = $boolean;
        if (strpos($name, ".") === false) {
            $q->{$method}($q->getModel()->getTable() . "." . $name, $operator, $value);
        } else {
            $names = explode('.', $name);
            $column = array_pop($names);
            $q->{$method . 'Relation'}(implode(".", $names), $column, $operator, $value);
        }
    }

    private function getRelationJoins(Builder $query): Builder
    {
        $this->relationAliases = [];
        $selects = [$query->getModel()->getTable() . '.*'];

        //Account For Count and other types of computed columns
        if (!empty($query->getQuery()->columns)) {
            foreach ($query->getQuery()->columns as $header) {
                if (!is_string($header) && get_class($header) == 'Illuminate\Database\Query\Expression' ){
                    $selects[] = $header;
                }
            }
        }

        $i = 0;

        //Rest of relations and so on
        foreach ($this->getHeader() as $header => $headerName) {
            if (strpos($header, ".") === false) {
                continue;
            }

            $relation = null;
            $model = $query->getModel();
            $connection = explode('.', $header);
            $relationName = array_pop($connection);

            foreach ($connection as $key => $relationProperty) {
                $relationProperty = Str::camel($relationProperty);
                $usingModel = empty($relation) ? $model : $relation->getModel();

                if (!method_exists($usingModel, $relationProperty)) {
                    break;
                }

                $relation = $usingModel->$relationProperty();

                if ($relation instanceof BelongsTo) {
                    $path = implode('.', array_slice($connection, 0, $key + 1));

                    if (isset($this->relationAliases[$path])) {
                        $asName = $this->relationAliases[$path];
                    } else {
                        $relatedTable = $relation->getModel()->getTable();
                        $asName = $relatedTable . '_' . $i++;
                        $parentAlias = $key === 0
                            ? $query->getModel()->getTable()
                            : $this->relationAliases[implode('.', array_slice($connection, 0, $key))];

                        $query->leftJoin(
                            $relatedTable . ' as ' . $asName,
                            $asName . '.' . $relation->getOwnerKeyName(),
                            '=',
                            $parentAlias . '.' . $relation->getForeignKeyName()
                        );

                        $this->relationAliases[$path] = $asName;
                    }

                    if ($key === count($connection) - 1) {
                        $selects[] = $asName . '.' . $relationName . ' AS ' . $header;
                    }
                } elseif ($relation instanceof HasOne) {
                    $relatedTable = $relation->getModel()->getTable();
                    //TODO: FIX OTHER RELATIONS
                }
            }
        }

        //Account for Select Bad behavior
        $query->getQuery()->columns = $selects;
        return $query;
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
        $relationName = array_pop($connection);
        $path = implode('.', $connection);

        if (isset($this->relationAliases[$path])) {
            return $this->relationAliases[$path] . '.' . $relationName;
        }

        // Fallback to resolving table directly when alias is missing
        foreach ($connection as $relationProperty) {
            $relationProperty = Str::camel($relationProperty);
            $relation = empty($relation)
                ? $query->getModel()->$relationProperty()
                : $relation->getModel()->$relationProperty();
        }

        $relatedTable = $relation->getModel()->getTable();
        return $relatedTable . '.' . $relationName;
    }
}
