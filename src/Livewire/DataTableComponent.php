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

    public $useUrl = null;

    // Transformation of the whole row on input (optional)
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

    public function mount()
    {
        $this->useUrl ??= !request()->hasHeader('X-Livewire');
    }

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
            throw new \RuntimeException(__('DataTable dataset cannot be empty.'));
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
        //etc....
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
        if(!$this->useUrl) return [];

        $queryStrings = [];
        if ($this->paginated == true) {
            $queryStrings['currentPage'] = ['except' => 0];
        }
        if ($this->searchable == true) {
            $queryStrings[] = 'searchValue';
        }
        // With load-on-scroll, itemsPerPage keeps growing with every scroll-load - in the URL
        // it would accumulate, and a refresh would immediately load hundreds of rows.
        if ($this->itemsPerPage != 0 && !method_exists($this, 'loadMore')) {
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
        $headers = array_keys($this->getHeader());

        // Filter and search first, on the original values
        $filtered = $this->filterArrayDataset($dataset);

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

        // Update totals before pagination
        $filtered = array_values($filtered);
        $this->itemsTotal = count($filtered);

        // Sort on the transformed dataset. Only row indexes are sorted, so pagination
        // can pick the page without copying every row into a new order first.
        if ($this->sortable && !empty($this->sortBy)) {
            $order = $this->sortedArrayOrder($filtered, $this->sortBy, strtolower($this->sortDirection) === 'desc' ? -1 : 1);

            // Paginate last
            if ($this->paginated != false) {
                $from = max(0, $this->itemsPerPage * ($this->currentPage - 1));
                $order = array_slice($order, $from, $this->itemsPerPage);
            }

            $page = [];
            foreach ($order as $idx) {
                $page[] = $filtered[$idx];
            }

            return $page;
        }

        // Paginate last
        if ($this->paginated != false) {
            $from = max(0, $this->itemsPerPage * ($this->currentPage - 1));
            $filtered = array_slice($filtered, $from, $this->itemsPerPage);
        }

        return $filtered;
    }

    /**
     * Applies fulltext search and headerFilter values to an array dataset.
     *
     * Counterpart of UseDatabase::applyFilters() for the array driver - also used by
     * HasBulkActions::selectableKeys(), so "select all" matches what the table shows.
     */
    protected function filterArrayDataset(array $dataset): array
    {
        $searchTerm = $this->searchTerm();
        $searchActive = $this->searchable && $searchTerm !== '';
        $filters = $this->filterable && !empty($this->headerFilter) ? $this->activeArrayFilters() : [];

        if (!$searchActive && empty($filters)) {
            return $dataset;
        }

        $searchNeedle = mb_strtolower($searchTerm);
        // Same fallback as setDefaults(), for calls made before the first render.
        $searchableSet = array_flip($this->searchableColumns ?: array_keys($this->getHeader()));

        $filtered = [];
        foreach ($dataset as $row) {
            foreach ($filters as $col => [$type, $a, $b]) {
                if (!array_key_exists($col, $row)) {
                    continue;
                }
                $value = $row[$col];

                if ($type === 'text') {
                    if (mb_stripos((string)$value, $a) === false) {
                        continue 2;
                    }
                } elseif ($type === 'select') {
                    if ($value != $a) {
                        continue 2;
                    }
                } elseif ($type === 'multiselect') {
                    // Loose comparison, same as select - values from the browser are strings.
                    if (!in_array($value, $a)) {
                        continue 2;
                    }
                } else {
                    $valTs = is_numeric($value) ? (int)$value : @strtotime((string)$value);
                    if ($a !== null && $valTs !== false && $valTs < $a) {
                        continue 2;
                    }
                    if ($b !== null && $valTs !== false && $valTs > $b) {
                        continue 2;
                    }
                }
            }

            if ($searchActive) {
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
                    continue;
                }
            }

            $filtered[] = $row;
        }

        return $filtered;
    }

    /**
     * Header filters prepared once per request instead of once per row:
     * [column => [type, text needle | select value | from timestamp, to timestamp]].
     * Filters that cannot exclude any row are left out.
     */
    private function activeArrayFilters(): array
    {
        $filtersMeta = $this->resolvedHeaderFilters();
        $headerSet = array_flip(array_keys($this->getHeader()));

        $active = [];
        foreach ($this->headerFilter as $col => $filterVal) {
            // Columns outside the headers have always been treated as text filters.
            $type = isset($headerSet[$col]) ? ($filtersMeta[$col]['type'] ?? 'text') : 'text';

            if ($type === 'text') {
                if (is_array($filterVal)) {
                    continue;
                }
                $needle = trim((string)$filterVal);
                if ($needle !== '') {
                    $active[$col] = [
                        'text',
                        $needle,
                        null,
                    ];
                }
            } elseif ($type === 'select') {
                if ($filterVal !== '') {
                    $active[$col] = [
                        'select',
                        $filterVal,
                        null,
                    ];
                }
            } elseif ($type === 'multiselect') {
                $values = $this->multiselectValues($filterVal);
                if (!empty($values)) {
                    $active[$col] = [
                        'multiselect',
                        $values,
                        null,
                    ];
                }
            } elseif (in_array($type, ['date', 'time', 'datetime-local'], true)) {
                $fromTs = (is_array($filterVal) && !empty($filterVal['from'])) ? @strtotime((string)$filterVal['from']) : null;
                $toTs   = (is_array($filterVal) && !empty($filterVal['to'])) ? @strtotime((string)$filterVal['to']) : null;
                if ($fromTs !== null || $toTs !== null) {
                    $active[$col] = [
                        'date',
                        $fromTs,
                        $toTs,
                    ];
                }
            }
        }

        return $active;
    }

    /**
     * Selected multiselect options without the empty ones; an empty result means no filter.
     */
    protected function multiselectValues(mixed $filterVal): array
    {
        return array_values(array_filter((array)$filterVal, fn ($v) => $v !== '' && $v !== null));
    }

    /**
     * Row indexes in sorted order. Sort keys are resolved once per row instead of
     * twice per comparison; the comparison itself is unchanged (numeric when both
     * values are numeric, strcmp otherwise; usort is stable for equal values).
     */
    private function sortedArrayOrder(array $rows, string $sortBy, int $dir): array
    {
        $numeric = [];
        $floats = [];
        $strings = [];
        foreach ($rows as $idx => $row) {
            $value = $this->valueByDot($row, $sortBy);
            $numeric[$idx] = is_numeric($value);
            $floats[$idx] = $numeric[$idx] ? (float)$value : 0.0;
            // Arrays were compared as 'Array' before as well, just with a warning.
            $strings[$idx] = is_array($value) ? 'Array' : (string)$value;
        }

        $order = array_keys($rows);
        usort($order, function ($a, $b) use ($numeric, $floats, $strings, $dir) {
            if ($numeric[$a] && $numeric[$b]) {
                return ($floats[$a] <=> $floats[$b]) * $dir;
            }

            return strcmp($strings[$a], $strings[$b]) * $dir;
        });

        return $order;
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

        if (method_exists($this, 'refreshSelectionState')) {
            $this->refreshSelectionState($this->dataset);
        }

        if (method_exists($this, 'refreshLoadMoreState')) {
            $this->refreshLoadMoreState();
        }

        if ($this->paginated != false && $this->itemsPerPage != 0) {
            $this->pagesTotal = (int) ceil($this->itemsTotal / $this->itemsPerPage);
        }

        if ($this->currentPage > $this->pagesTotal) {
            $this->updatedCurrentPage($this->pagesTotal);
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

    public function updatedCurrentPage(int $value)
    {
        $this->currentPage = $value;
    }

    /**
     * headers() memoized for the lifetime of the component instance - Livewire builds
     * a new instance for every request, so nothing is shared between requests.
     * In the array driver the default headers() calls dataset(), which would otherwise
     * run several times per render.
     *
     * NOTE: if an action changes state that headers() depends on after getHeader()
     * was already called in the same request, the cached value is returned.
     */
    public function getHeader(): array
    {
        return once(fn () => $this->headers());
    }

    /**
     * headerFilters() memoized the same way as getHeader() - definitions often load
     * select options from the database.
     */
    protected function resolvedHeaderFilters(): array
    {
        return once(fn () => $this->headerFilters());
    }

	public function renderCasts(): array
    {
        return [];
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
        // Selection is only available with the HasBulkActions trait; selectionEnabled()
        // also respects canSelect(), so it can be gated by a permission.
        $hasBulkActions = method_exists($this, 'bulkActions') && method_exists($this, 'selectionEnabled');
        $selectable = $hasBulkActions && $this->selectionEnabled();

        return view($this->viewName, [
            'dataset'              => $this->getData(),
            'headers'              => $this->getHeader(),
            'footers'              => $this->footers(),
            'headerFilters'        => !empty($this->filterable) ? $this->resolvedHeaderFilters() : null,
			'renderCasts' => $this->renderCasts(),
            'selectable'           => $selectable,
            'selected'             => $selectable ? $this->selected : [],
            'selectPage'           => $selectable && $this->pageSelected(),
            'partiallySelected'    => $selectable && $this->pagePartiallySelected(),
            'bulkActions'          => $selectable ? $this->bulkActions() : [],
            'selectAllAcrossPages' => $selectable && $this->selectAllAcrossPages(),
            'keyPropery'           => $this->keyPropery,
            // Explicit flag instead of detecting it via the existence of the canLoadMore variable in the view.
            'loadOnScroll'         => method_exists($this, 'loadMore'),
        ]);
    }

    public function updatedSearchValue()
    {
        $this->currentPage = 1;
    }

    /**
     * Search value without leading/trailing whitespace, so e.g. a pasted " term "
     * still matches. The bound $searchValue is left untouched while typing.
     */
    protected function searchTerm(): string
    {
        return trim($this->searchValue);
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
