# Changelog

## 2.9.1

### Výkon
- `tbody.blade.php` rozhoduje o vykreslení sloupců jednou pro celou tabulku, ne pro každou buňku.
  Livewire vkládá kolem každého `@if`/`@foreach` dva morph markery (HTML komentáře). Na řádek jich
  bylo ~26 bez akcí a ~39 s akcemi. Teď zbývají jen markery akcí a markery z komponent, které vrací
  `renderColumnX()`. Vykreslený obsah buněk je stejný (ověřeno snapshoty ze staré šablony),
  výsledky měření jsou v `benchmarks/MARKERS.md`.
- Buňka výběru (`x-datatable-selection-cell`) se renderuje jen u tabulek s výběrem.

### Poznámky
- Render cast se vytvoří jednou pro sloupec, ne pro každou buňku. Casty mají být bezstavové
  (rozhraní `RenderCast`), stavový cast by teď sdílel instanci mezi řádky.
- Kdo má publikovanou vlastní kopii `tbody.blade.php`, zůstává u své verze a o optimalizaci přijde.

## 2.9.0

### Bezpečnost
- SQL injection přes `sortBy` / `sortDirection` (`orderByRaw()`).
- Volání metod modelu (např. `truncate()`) přes podvržené `searchableColumns`.
- Podvržený `viewName`, záporné `itemsPerPage` z URL, CSV injection v exportu.

### Opravy
- Select-all v array driveru respektuje hledání a filtry.
- Datumový filtr v DB driveru, filtr `multiselect`, doslovné `%` a `_` v hledání.
- Stav tabulky v URL (query string) se ukládá a obnovuje.
- CSV export obsahuje všechny vyfiltrované řádky a nepadá na prázdném výsledku.

### Výkon
- `getHeader()` a `headerFilters()` jednou za request, rychlejší filtrování a řazení v array driveru.
