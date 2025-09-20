<?php

namespace SteelAnts\DataTable\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Str;

class DataTableComponent extends Component
{
    /* RUNTIME VARIABLES */
    public $dataset = [];
    public $actions = [];

    public int $pagesTotal = 1;
    public int $currentPage = 1;
    public int $itemsTotal = 1;

    // Enable sorting
    public bool $sortable = true;
    public array $sortableColumns = [];
    public string $sortBy = '';
    public string $sortDirection = 'asc';

    // Enable pagination
    public bool $paginated = true;
    public int $itemsPerPage = 10;

    // Enable fulltext search
    public bool $searchable = false;
    public array $searchableColumns = [];
    public string $searchValue = '';

    public bool $filterable = false;
    public array $filter = [];
    public array $headerFilter = [];

    // Other config
    public string $tableClass = 'table align-middle';
    public string $viewName = 'datatable::data-table';
    public bool $showHeader = true;

    // TODO: do i need this?
    public string $keyPropery = 'id';

    // Transformace whole row on input (optional)
    // Returns associative array
    // public function row(Model $row) : array
    // {
    //     return [
    //         'id' => $row->id,
    //     ];
    // }

    // Transform one column on input (optional)
    // public function columnFoo(mixed $column) : mixed
    // {
    //      return $column;
    // }


    // Transform whole row on output (optional)
    // !!! NOTE: values are rendered with {!! !!}, manually escape values
    // public function renderRow(array $row) : array
    // {
    //     return [
    //         'id' => e($row['id'])
    //     ];
    // }

    // Transform one column on output (optional)
    // !!! NOTE: values are rendered with {!! !!}, manually escape values
    // public function renderColumnFoo(mixed $value, array $row) : string
    // {
    //     return e($value);
    // }



    public function dataset(): array
    {
        return [];
    }

    /**
     * @throws \RuntimeException
     */
    public function headers(): array
    {
        $data = $this->dataset();

        if (empty($data)) {
            throw new \RuntimeException('DataTable dataset cannot be empty.');
        }

        $keys = array_keys($data[0]);

        return array_combine($keys, $keys);
    }

    public function footers(): array
    {
        return [];
        // $footer = [];
        // $footer[] = "Count";
        // for ($item=1; $item < count($this->dataset[0]); $item++) {
        //     $footer[] = "";
        // }
        // $footer[] = count($this->dataset);
        // return $footer;
    }

    public function headerFilters(): array
    {
        //only select and inputs
        //select - ['table name' => ['type' => 'select', 'values' => ['value' => 'name', ''value2' => 'name2']]]
        //text - ['table name' => ['type' => 'text']
        //datetime - ['table name' => ['type' => 'datetime']]
        //atd....
        return array_fill_keys(array_keys($this->getHeader()), ['type' => 'text']);
    }

    public function updatedHeaderFilter()
    {
    }

    public function updatedItemsPerPage()
    {
        $this->currentPage = 1;
    }

    // TODO
    // public function updatedCurrentPage()
    // {
    //     $this->getData(true);
    // }

    public function queryString(): array
    {
        $queryStrings = [];
        if ($this->paginated == true) {
            $queryStrings['currentPage'] = ['except' => 0];
        }
        if ($this->searchable == true) {
            $queryStrings[] = 'searchValue';
        }
        if ($this->itemsPerPage != 0) {
            $queryStrings[] = 'itemsPerPage';
        }
        if ($this->sortable != false) {
            $queryStrings[] = 'sortBy';
            if (!empty($this->sortBy)) {
                $queryStrings[] = 'sortDirection';
            }
        }
        return $queryStrings;
    }

    private function getDatasetFromArray($dataset): array
    {
        // Cache headers and filter metadata once
        $headers = array_keys($this->getHeader());
        $filtersMeta = $this->filterable ? $this->headerFilters() : [];
        $filterTypes = [];
        foreach ($headers as $h) {
            $filterTypes[$h] = $filtersMeta[$h]['type'] ?? 'text';
        }

        $searchActive = $this->searchable && $this->searchValue !== '';
        $searchNeedle = $searchActive ? mb_strtolower($this->searchValue) : '';
        $searchableSet = $this->searchable ? array_flip($this->searchableColumns) : [];

        // Filter and search first
        $filtered = [];
        if (($this->filterable && !empty($this->headerFilter)) || $searchActive) {
            foreach ($dataset as $row) {
                $keep = true;

                if ($this->filterable && !empty($this->headerFilter)) {
                    foreach ($row as $col => $value) {
                        if (!array_key_exists($col, $this->headerFilter)) {
                            continue;
                        }
                        $type = $filterTypes[$col] ?? 'text';
                        $filterVal = $this->headerFilter[$col];
                        if ($type === 'text') {
                            if ($filterVal !== '' && mb_stripos((string)$value, (string)$filterVal) === false) {
                                $keep = false;
                                break;
                            }
                        } elseif ($type === 'select') {
                            if ($filterVal !== '' && $value != $filterVal) {
                                $keep = false;
                                break;
                            }
                        } elseif (in_array($type, ['date', 'time', 'datetime-local'], true)) {
                            $valTs = is_numeric($value) ? (int)$value : @strtotime((string)$value);
                            $fromTs = (is_array($filterVal) && !empty($filterVal['from'])) ? @strtotime((string)$filterVal['from']) : null;
                            $toTs   = (is_array($filterVal) && !empty($filterVal['to'])) ? @strtotime((string)$filterVal['to']) : null;
                            if ($fromTs !== null && $valTs !== false && $valTs < $fromTs) {
                                $keep = false;
                                break;
                            }
                            if ($toTs !== null && $valTs !== false && $valTs > $toTs) {
                                $keep = false;
                                break;
                            }
                        }
                    }
                }

                if ($keep && $searchActive) {
                    $matched = false;
                    foreach ($row as $col => $value) {
                        if (!isset($searchableSet[$col])) {
                            continue;
                        }
                        if ($value !== null && $value !== '' && mb_stripos((string)$value, $searchNeedle) !== false) {
                            $matched = true;
                            break;
                        }
                    }
                    if (!$matched) {
                        $keep = false;
                    }
                }

                if ($keep) {
                    $filtered[] = $row;
                }
            }
        } else {
            $filtered = $dataset;
        }

        // Transform rows/columns once, with cached column method lookups
        if (method_exists($this, 'row')) {
            $columnMethodCache = [];
            foreach ($headers as $header) {
                $method = 'column' . ucfirst(Str::camel(str_replace('.', '_', $header)));
                $columnMethodCache[$header] = method_exists($this, $method) ? $method : null;
            }

            foreach ($filtered as $idx => $item) {
                $tempRow = $this->row($item);
                foreach ($tempRow as $col => $property) {
                    $method = $columnMethodCache[$col] ?? null;
                    if ($method) {
                        $tempRow[$col] = $this->{$method}($property);
                    }
                }
                $filtered[$idx] = $tempRow;
            }
        }

        // Sort on the transformed dataset
        if ($this->sortable && !empty($this->sortBy)) {
            $sortBy = $this->sortBy;
            $dir = strtolower($this->sortDirection) === 'desc' ? -1 : 1;
            usort($filtered, function ($a, $b) use ($sortBy, $dir) {
                $av = $this->valueByDot($a, $sortBy);
                $bv = $this->valueByDot($b, $sortBy);
                if (is_numeric($av) && is_numeric($bv)) {
                    $cmp = (float)$av <=> (float)$bv;
                } else {
                    $cmp = strcmp((string)$av, (string)$bv);
                }
                return $cmp * $dir;
            });
        }

        // Update totals before pagination
        $this->itemsTotal = count($filtered);

        // Paginate last
        if ($this->paginated != false) {
            $from = max(0, $this->itemsPerPage * ($this->currentPage - 1));
            $filtered = array_slice($filtered, $from, $this->itemsPerPage);
        }

        return array_values($filtered);
    }

    private function getData($force = false): array
    {
        $this->setDefaults();

        $this->itemsTotal = 0;
        if (method_exists($this, "query")) {
            $this->dataset = $this->datasetFromDB($this->query());
        } else {
            $this->dataset = $this->getDatasetFromArray($this->dataset());
        }
        if (method_exists($this, "actions")) {
            foreach ($this->dataset as $tempRow) {
                $this->actions[] = $this->actions($tempRow);
            }
        }

        if ($this->paginated != false && $this->itemsPerPage != 0) {
            $this->pagesTotal = round(ceil($this->itemsTotal / $this->itemsPerPage));
        }

        if ($this->currentPage > $this->pagesTotal) {
            $this->dispatch('updatedCurrentPage', $this->pagesTotal);
        }

        return $this->dataset;
    }

    private function setDefaults()
    {
        $this->actions = [];
        if ($this->sortable == true && $this->sortableColumns == []) {
            $this->sortableColumns = array_keys($this->getHeader());
        }

        if ($this->searchable == true && $this->searchableColumns == []) {
            $this->searchableColumns = array_keys($this->getHeader());
        }
    }

    #[On('updatedCurrentPage')]
    public function updatedCurrentPage(int $value)
    {
        $this->currentPage = $value;
    }

    public function getHeader(): array
    {
        return $this->headers();
    }

    // public function actions($item): array
    // {
    //     return [
    //         [
    //             'type' => "url",
    //             'url' => route('test.form', ['modelId' => $item['id']]),
    //             'text' => "edit",
    //             'iconClass' => 'fas fa-pen',
    //         ],
    //         [
    //             'type' => "livewire",
    //             'action' => 'showModal',
    //             'parameters' => [
    //                 'task' => $item['id'],
    //             ],
    //         ],
    //     ];
    // }

    public function render()
    {
        return view($this->viewName, [
            'dataset'       => $this->getData(),
            'headers'       => $this->getHeader(),
            'footers'       => $this->footers(),
            'headerFilters' => !empty($this->filterable) ? $this->headerFilters() : null,
        ]);
    }

    public function updatedSearchValue()
    {
        $this->currentPage = 1;
    }

    private function valueByDot(array $row, string $key)
    {
        if ($key === '' || strpos($key, '.') === false) {
            return $row[$key] ?? null;
        }
        static $splitCache = [];
        $parts = $splitCache[$key] ??= explode('.', $key);
        $value = $row;
        foreach ($parts as $p) {
            if (!is_array($value) || !array_key_exists($p, $value)) {
                return null;
            }
            $value = $value[$p];
        }
        return $value;
    }
}
