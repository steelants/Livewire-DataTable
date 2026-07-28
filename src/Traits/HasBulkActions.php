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
     * Zapina sloupec s checkboxy. Nastavuje se v bootHasBulkActions(),
     * takze pouziti traitu vyber zapne samo.
     */
    public bool $selectable = false;

    /**
     * Vybrane klice radku. Hodnoty z HTML jsou stringy, drzime je tak i tady.
     *
     * POZOR: je to public Livewire property, takze do ni klient zapise cokoli.
     * K datum se proto chod jen pres selectedQuery() / eachSelected().
     *
     * @var list<string>
     */
    public array $selected = [];

    /**
     * Klice radku na aktualni strance - podklad pro checkbox v hlavicce.
     *
     * Persistovat se musi: toggleSelectPage() bezi pred renderem, takze potrebuje
     * klice z predchoziho vykresleni a znovu nacitat dataset by bylo drahe.
     * Klient do nich muze zapsat cokoli, ale ovlivni tim jen obsah $selected,
     * ktery se stejne vzdy validuje pres selectedQuery().
     *
     * @var list<string>
     */
    public array $visibleSelectionKeys = [];

    /**
     * Povoli "vybrat vse z filtru" (napric strankami). Vypnute = vybirat lze
     * jen po strankach, obdoba selectCurrentPageOnly() ve Filamentu.
     */
    public bool $selectAllAcrossPages = false;

    /**
     * Strop pro selectAllFiltered(). 0 = bez limitu.
     *
     * Velke vybery nafukuji Livewire payload a naraze na limit parametru
     * prepared statementu (u MySQL kolem 65 000).
     */
    public int $selectAllLimit = 0;

    /**
     * Zahodit vyber pri zmene filtru, hledani nebo razeni. Jinak by hromadna
     * akce sahla na radky, ktere uzivatel uz nevidi.
     */
    public bool $clearSelectionOnFilter = true;

    /**
     * Velikost davky pri zpracovani vyberu v eachSelected().
     */
    public int $bulkChunkSize = 100;

    public function bootHasBulkActions(): void
    {
        $this->selectable = true;
    }

    /**
     * Definice hromadnych akci. Stejny tvar jako actions(), jen bez 'parameters'
     * mirenych na konkretni radek.
     *
     * Vrat prazdne pole, kdyz uzivatel nema opravneni - lista se pak nezobrazi
     * a callBulkAction() nema co spustit.
     *
     * @return list<array<string,mixed>>
     */
    public function bulkActions(): array
    {
        return [];
    }

    /**
     * Prepis v komponente, kdyz ma byt vyber podminen opravnenim.
     */
    public function canSelect(): bool
    {
        return true;
    }

    /**
     * Jedna brana pro vsechny mutace vyberu.
     */
    public function selectionEnabled(): bool
    {
        return $this->selectable && $this->canSelect();
    }

    /**
     * Livewire trait hook - vola se po zmene jakekoli property.
     *
     * Zamerne to NENI updatedHeaderFilter(): tu metodu deklaruje
     * DataTableComponent i aplikacni potomci, a metoda ve tride prebije metodu
     * z traitu, takze by se cisteni vyberu tise preskocilo.
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
     * Prepne vyber cele aktualni stranky.
     *
     * Zamerne akce, ne wire:model property - "je stranka vybrana" je odvozeny
     * stav z $selected a klicu na strance (viz pageSelected()). Kdyz se drzel
     * jako persistovana property, vozil se v payloadu zbytecne a rozchazel se
     * se skutecnym vyberem.
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
     * Jsou vsechny radky na aktualni strance vybrane? Podklad pro stav
     * checkboxu v hlavicce.
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
     * Je vybrana jen cast aktualni stranky? Pro indeterminate stav hlavicky.
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
     * Prepocita stav vyberu proti aktualne vykreslenym radkum. Vola se
     * z DataTableComponent::getData() po nacteni datasetu.
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
     * Spusti hromadnou akci. Prijima nazev akce (preferovane) nebo jeji index
     * v bulkActions() kvuli zpetne kompatibilite.
     *
     * Akce se dohledava v aktualnim bulkActions(), takze co uzivatel nevidi,
     * nejde ani zavolat.
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
     * Vybere vsechny radky odpovidajici aktualnim filtrum, i mimo aktualni stranku.
     *
     * @return bool false = vyber se neprovedl (vypnuto nebo prekrocen selectAllLimit)
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
     * Dotaz omezeny na vybrane radky.
     *
     * Vzdy vychazi z query() komponenty, takze vyber nemuze uniknout z jejiho
     * scopu - podvrzene ID z prohlizece se sem nedostane.
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
     * Projde vybrane radky po davkach.
     *
     * Callback vraci:
     *   true (nebo nic) = zpracovano
     *   false           = preskoceno
     *   string          = preskoceno s duvodem (seskupi se do BulkResult::$reasons)
     *
     * Vyjimka z callbacku oznaci radek jako failed, nahlasi ji pres report()
     * a pokracuje dalsim radkem - jeden vadny radek neshodi celou akci.
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
     * Jednotna hlaska k vysledku hromadne akce.
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
     * Hodnota z radku. Radek je bud pole (UseDatabase, dataset()) nebo model
     * (UseDatabaseEloquent) - data_get() zvlada oboji vcetne teckove notace.
     *
     * @param array<string,mixed>|Model $row
     */
    protected function rowValue(array|Model $row, string $key): mixed
    {
        return data_get($row, $key);
    }

    /**
     * Klice radku odpovidajici aktualnim filtrum.
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
     * Deduplikuje a vzdy vraci seznam stringu.
     *
     * PHP prevadi numericke stringy pouzite jako klice pole na inty, takze
     * array_keys() po deduplikaci vraci inty - proto se na konci pretypovava.
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
     * @param int|string $action Nazev akce nebo jeji index v bulkActions().
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
