<?php

namespace SteelAnts\DataTable\Traits;

use Illuminate\Support\Arr;

trait HasBulkActions
{
    public bool $selectable = false;
    public bool $selectPage = false;
    public array $selected = [];
    public array $visibleSelectionKeys = [];

    public function bootHasBulkActions()
    {
        $this->selectable = true;
    }

    public function bulkActions(): array
    {
        return [];
    }

    public function updatedSelectPage(bool $value)
    {
        if ($value) {
            $this->selected = $this->normalizeSelectedValues(
                array_merge($this->selected, $this->visibleSelectionKeys)
            );
        } else {
            $remove = array_flip($this->visibleSelectionKeys);
            $this->selected = array_values(array_filter(
                $this->selected,
                fn ($key) => !isset($remove[$key])
            ));
        }
    }

    public function updatedSelected()
    {
        $this->selected = $this->normalizeSelectedValues($this->selected);
        $this->syncSelectPageState();
    }

    public function refreshSelectionState(array $dataset): void
    {
        $this->visibleSelectionKeys = $this->resolveSelectionKeys($dataset);
        $this->selected = $this->normalizeSelectedValues($this->selected);
        $this->syncSelectPageState();
    }

    public function callBulkAction(int $index)
    {
        $action = $this->bulkActions()[$index] ?? null;

        if ($action === null || ($action['type'] ?? null) !== 'livewire') {
            return;
        }

        $method = $action['action'];
        $parameters = $action['parameters'] ?? [];
        $parameters = is_array($parameters) ? $parameters : [$parameters];

        $this->{$method}($this->selected, ...$parameters);
    }

    public function clearSelection()
    {
        $this->selected = [];
        $this->selectPage = false;
    }

    public function selectedCount(): int
    {
        return count($this->selected);
    }

    private function resolveSelectionKeys(array $dataset): array
    {
        $keys = [];

        foreach ($dataset as $row) {
            $key = $this->resolveSelectionKey($row);
            if ($key !== null) {
                $keys[$key] = true;
            }
        }

        return array_keys($keys);
    }

    private function resolveSelectionKey(array $row): ?string
    {
        $value = Arr::get($row, $this->keyPropery);

        if ($value === null || $value === '' || is_array($value) || is_object($value)) {
            return null;
        }

        return (string) $value;
    }

    private function normalizeSelectedValues(array $selected): array
    {
        $normalized = [];

        foreach ($selected as $value) {
            if ($value === null || $value === '' || is_array($value) || is_object($value)) {
                continue;
            }
            $normalized[(string) $value] = true;
        }

        return array_keys($normalized);
    }

    private function syncSelectPageState(): void
    {
        if (!$this->selectable || empty($this->visibleSelectionKeys)) {
            $this->selectPage = false;
            return;
        }

        $selectedLookup = array_flip($this->selected);

        foreach ($this->visibleSelectionKeys as $key) {
            if (!isset($selectedLookup[$key])) {
                $this->selectPage = false;
                return;
            }
        }

        $this->selectPage = true;
    }
}
