# Changelog

## 3.0.2

### Opravy
- Hlavičkový checkbox výběru stránky zůstával po vyprázdnění výběru (např. po bulk akci) zaškrtnutý
  a další klik místo odznačení znovu označil celou stránku. Livewire morph přepisoval jen atribut
  `checked`, ne DOM vlastnost přepnutou klikem. Input má teď `wire:key` podle stavu výběru, takže se
  při změně vymění (platí i pro `indeterminate`).
- Tlačítko Export je na stejném řádku jako vyhledávání, zarovnané doprava. Dřív zabíralo celý
  vlastní řádek pod ním.

### Změny
- Ikony v šablonách a dokumentaci používají Font Awesome 7 (`fa-solid` místo `fas`). Projekty
  s Font Awesome 5 musí přejít na novější verzi.

## 3.0.0

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

## 2.9.0

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
