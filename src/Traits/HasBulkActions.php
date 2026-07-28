<?php

declare(strict_types=1);

namespace SteelAnts\DataTable\Traits;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use SteelAnts\DataTable\Support\BulkResult;
use Throwable;

trait HasBulkActions
{
    /**
     * Enables the checkbox column. Set in bootHasBulkActions(),
     * so using the trait turns on selection automatically.
     */
    public bool $selectable = false;

    /**
     * Selected row keys. Values coming from HTML are strings, so we keep them as strings here too.
     *
     * WARNING: this is a public Livewire property, so the client can write anything into it.
     * Access to the data must therefore always go through selectedQuery() / eachSelected().
     *
     * @var list<string>
     */
    public array $selected = [];

    /**
     * Row keys on the current page - the basis for the header checkbox state.
     *
     * Must be persisted: toggleSelectPage() runs before the render, so it needs
     * the keys from the previous render, and reloading the dataset would be expensive.
     * The client can write anything into it, but that only affects the contents of $selected,
     * which is always validated through selectedQuery() anyway.
     *
     * @var list<string>
     */
    public array $visibleSelectionKeys = [];

    /**
     * Enables "select all from filter" (across pages). Disabled = selection is possible
     * only per page, similar to selectCurrentPageOnly() in Filament.
     *
     * Config values are deliberately methods, not properties: PHP does not allow redeclaring
     * a trait property with a different default value (fatal error on composition),
     * so the property couldn't be overridden in the component.
     */
    public function selectAllAcrossPages(): bool
    {
        return false;
    }

    /**
     * Cap for selectAllFiltered(). 0 = no limit.
     *
     * Large selections bloat the Livewire payload and can hit the parameter limit
     * of prepared statements (around 65,000 for MySQL).
     */
    public function selectAllLimit(): int
    {
        return 0;
    }

    /**
     * Discard the selection when the filter, search, or sorting changes. Otherwise the bulk
     * action would touch rows the user can no longer see.
     */
    public function clearSelectionOnFilter(): bool
    {
        return true;
    }

    /**
     * Chunk size when processing the selection in eachSelected().
     */
    public function bulkChunkSize(): int
    {
        return 100;
    }

    public function bootHasBulkActions(): void
    {
        $this->selectable = true;
    }

    /**
     * Definition of bulk actions. Same shape as actions(), just without 'parameters'
     * targeted at a specific row.
     *
     * Return an empty array when the user lacks permission - the bar then won't be shown
     * and callBulkAction() has nothing to run.
     *
     * @return list<array<string,mixed>>
     */
    public function bulkActions(): array
    {
        return [];
    }

    /**
     * Override in the component when selection should be gated by a permission.
     */
    public function canSelect(): bool
    {
        return true;
    }

    /**
     * A single gate for all selection mutations.
     */
    public function selectionEnabled(): bool
    {
        return $this->selectable && $this->canSelect();
    }

    /**
     * Livewire trait hook - called after any property changes.
     *
     * This is deliberately NOT updatedHeaderFilter(): that method is declared by
     * DataTableComponent and application subclasses, and a class method overrides the trait's,
     * so clearing the selection would silently be skipped.
     */
    public function updatedHasBulkActions(string $name, mixed $value): void
    {
        if (!$this->clearSelectionOnFilter) {
            return;
        }

        foreach (['headerFilter', 'searchValue', 'sortBy', 'sortDirection'] as $watched) {
            if ($name === $watched || str_starts_with($name, $watched . '.')) {
                $this->clearSelection();

                return;
            }
        }
    }

    /**
     * Toggles the selection of the whole current page.
     *
     * Deliberately an action, not a wire:model property - "is the page selected" is a state
     * derived from $selected and the page's keys (see pageSelected()). When it was kept
     * as a persisted property, it was carried in the payload needlessly and drifted out of sync
     * with the actual selection.
     */
    public function toggleSelectPage(): void
    {
        if (!$this->selectionEnabled()) {
            return;
        }

        if ($this->pageSelected()) {
            $remove = array_flip($this->visibleSelectionKeys);

            $this->selected = array_values(array_filter(
                $this->selected,
                fn ($key) => !isset($remove[$key])
            ));

            return;
        }

        $this->selected = $this->normalizeSelectedValues(
            array_merge($this->selected, $this->visibleSelectionKeys)
        );
    }

    /**
     * Are all rows on the current page selected? Basis for the
     * header checkbox state.
     */
    public function pageSelected(): bool
    {
        if (empty($this->visibleSelectionKeys)) {
            return false;
        }

        $selectedLookup = array_flip($this->selected);

        foreach ($this->visibleSelectionKeys as $key) {
            if (!isset($selectedLookup[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Is only part of the current page selected? For the header's indeterminate state.
     */
    public function pagePartiallySelected(): bool
    {
        if (empty($this->visibleSelectionKeys) || $this->pageSelected()) {
            return false;
        }

        $selectedLookup = array_flip($this->selected);

        foreach ($this->visibleSelectionKeys as $key) {
            if (isset($selectedLookup[$key])) {
                return true;
            }
        }

        return false;
    }

    public function updatedSelected(): void
    {
        if (!$this->selectionEnabled()) {
            $this->clearSelection();

            return;
        }

        $this->selected = $this->normalizeSelectedValues($this->selected);
    }

    /**
     * Recalculates the selection state against the currently rendered rows. Called
     * from DataTableComponent::getData() after the dataset is loaded.
     *
     * @param array<int,array<string,mixed>|Model> $dataset
     */
    public function refreshSelectionState(array $dataset): void
    {
        if (!$this->selectionEnabled()) {
            $this->visibleSelectionKeys = [];
            $this->clearSelection();

            return;
        }

        $this->visibleSelectionKeys = $this->resolveSelectionKeys($dataset);
        $this->selected = $this->normalizeSelectedValues($this->selected);
    }

    /**
     * Runs a bulk action. Accepts the action name (preferred) or its index
     * in bulkActions() for backwards compatibility.
     *
     * The action is looked up in the current bulkActions(), so what the user can't see
     * cannot be called either.
     */
    public function callBulkAction(int|string $action): mixed
    {
        if (!$this->selectionEnabled()) {
            return null;
        }

        $definition = $this->resolveBulkAction($action);

        if ($definition === null || ($definition['type'] ?? 'livewire') !== 'livewire') {
            return null;
        }

        $method = $definition['action'] ?? null;

        if (!is_string($method) || $method === '' || !method_exists($this, $method)) {
            return null;
        }

        $parameters = $definition['parameters'] ?? [];
        $parameters = is_array($parameters) ? $parameters : [$parameters];

        return $this->{$method}($this->selected, ...$parameters);
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function selectedCount(): int
    {
        return count($this->selected);
    }

    public function isSelected(mixed $key): bool
    {
        $key = $this->normalizeSelectionValue($key);

        return $key !== null && in_array($key, $this->selected, true);
    }

    /**
     * Selects all rows matching the current filters, even outside the current page.
     *
     * @return bool false = the selection was not performed (disabled or selectAllLimit exceeded)
     */
    public function selectAllFiltered(): bool
    {
        if (!$this->selectionEnabled() || !$this->selectAllAcrossPages) {
            return false;
        }

        $keys = $this->selectableKeys();

        if ($this->selectAllLimit > 0 && count($keys) > $this->selectAllLimit) {
            return false;
        }

        $this->selected = $keys;

        return true;
    }

    /**
     * Query restricted to the selected rows.
     *
     * Always builds on the component's query(), so the selection can never escape its
     * scope - a forged ID from the browser won't get through here.
     */
    public function selectedQuery(): Builder
    {
        if (!method_exists($this, 'query')) {
            throw new RuntimeException(
                'selectedQuery() requires a query() method - use it with UseDatabase or UseDatabaseEloquent.'
            );
        }

        $query = $this->query();

        return $query->whereIn($query->getModel()->getQualifiedKeyName(), $this->selected);
    }

    /**
     * Iterates the selected rows in chunks.
     *
     * The callback returns:
     *   true (or nothing) = processed
     *   false             = skipped
     *   string            = skipped with a reason (grouped into BulkResult::$reasons)
     *
     * An exception from the callback marks the row as failed, reports it via report()
     * and continues with the next row - one bad row doesn't bring down the whole action.
     */
    public function eachSelected(Closure $callback): BulkResult
    {
        if (empty($this->selected)) {
            return new BulkResult();
        }

        $ok = 0;
        $skipped = 0;
        $failed = 0;
        $reasons = [];

        $query = $this->selectedQuery();
        $model = $query->getModel();

        $query->chunkById(
            max(1, $this->bulkChunkSize),
            function ($rows) use ($callback, &$ok, &$skipped, &$failed, &$reasons): void {
                foreach ($rows as $row) {
                    try {
                        $result = $callback($row);
                    } catch (Throwable $e) {
                        $failed++;
                        report($e);

                        continue;
                    }

                    if ($result === false) {
                        $skipped++;

                        continue;
                    }

                    if (is_string($result)) {
                        $skipped++;
                        $reasons[$result] = ($reasons[$result] ?? 0) + 1;

                        continue;
                    }

                    $ok++;
                }
            },
            $model->getQualifiedKeyName(),
            $model->getKeyName()
        );

        return new BulkResult($ok, $skipped, $failed, $reasons);
    }

    /**
     * A unified message for the bulk action result.
     */
    public function bulkResultMessage(BulkResult $result): string
    {
        if ($result->isEmpty()) {
            return __('Nothing to process.');
        }

        if (!$result->hasProblems()) {
            return __('Processed :count items.', ['count' => $result->ok]);
        }

        $message = __('Processed :ok of :total items.', [
            'ok'    => $result->ok,
            'total' => $result->total(),
        ]);

        if ($result->skipped > 0) {
            $message .= ' ' . __(':count skipped.', ['count' => $result->skipped]);

            $parts = [];
            foreach ($result->sortedReasons() as $reason => $count) {
                $parts[] = __($reason) . ' (' . $count . ')';
            }

            if (!empty($parts)) {
                $message .= ' ' . implode(', ', $parts);
            }
        }

        if ($result->failed > 0) {
            $message .= ' ' . __(':count failed.', ['count' => $result->failed]);
        }

        return $message;
    }

    /**
     * Value from a row. The row is either an array (UseDatabase, dataset()) or a model
     * (UseDatabaseEloquent) - data_get() handles both, including dot notation.
     *
     * @param array<string,mixed>|Model $row
     */
    protected function rowValue(array|Model $row, string $key): mixed
    {
        return data_get($row, $key);
    }

    /**
     * Row keys matching the current filters.
     *
     * @return list<string>
     */
    protected function selectableKeys(): array
    {
        if (!method_exists($this, 'query')) {
            return $this->resolveSelectionKeys($this->dataset());
        }

        $query = $this->query();
        $keyName = $query->getModel()->getQualifiedKeyName();

        if (method_exists($this, 'applyFilters')) {
            $query = $this->applyFilters($query);
        }

        return $this->stringifyKeys($query->pluck($keyName)->all());
    }

    /**
     * @param array<int,array<string,mixed>|Model> $dataset
     * @return list<string>
     */
    private function resolveSelectionKeys(array $dataset): array
    {
        $keys = [];

        foreach ($dataset as $row) {
            if (!is_array($row) && !$row instanceof Model) {
                continue;
            }

            $key = $this->resolveSelectionKey($row);

            if ($key !== null) {
                $keys[] = $key;
            }
        }

        return $this->stringifyKeys($keys);
    }

    /**
     * @param array<string,mixed>|Model $row
     */
    private function resolveSelectionKey(array|Model $row): ?string
    {
        return $this->normalizeSelectionValue($this->rowValue($row, $this->keyPropery));
    }

    /**
     * @param array<int,mixed> $selected
     * @return list<string>
     */
    private function normalizeSelectedValues(array $selected): array
    {
        return $this->stringifyKeys($selected);
    }

    private function normalizeSelectionValue(mixed $value): ?string
    {
        if ($value === null || $value === '' || is_array($value) || is_object($value) || is_bool($value)) {
            return null;
        }

        return (string) $value;
    }

    /**
     * Deduplicates and always returns a list of strings.
     *
     * PHP converts numeric strings used as array keys to ints, so
     * array_keys() after deduplication returns ints - hence the cast at the end.
     *
     * @param array<int,mixed> $keys
     * @return list<string>
     */
    private function stringifyKeys(array $keys): array
    {
        $unique = [];

        foreach ($keys as $key) {
            $key = $this->normalizeSelectionValue($key);

            if ($key === null) {
                continue;
            }

            $unique[$key] = true;
        }

        return array_values(array_map(
            static fn ($key): string => (string) $key,
            array_keys($unique)
        ));
    }

    /**
     * @param int|string $action Action name or its index in bulkActions().
     * @return array<string,mixed>|null
     */
    private function resolveBulkAction(int|string $action): ?array
    {
        $actions = $this->bulkActions();

        if (is_int($action)) {
            return $actions[$action] ?? null;
        }

        foreach ($actions as $definition) {
            if (($definition['action'] ?? null) === $action) {
                return $definition;
            }
        }

        return null;
    }
}
