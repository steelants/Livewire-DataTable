# Benchmark: morph markery v tbody

Tabulka s 8 sloupci: 4 plain, 2 render casty, 2× `renderColumnX()` (jeden vrací Blade komponentu
s vlastním `@if`, podobně jako `x-badge` v appce). Varianta „akce“ má 3 akce na řádek (každý pátý
řádek žádnou), „akce + výběr“ navíc `HasBulkActions`. Bez stránkování, renderují se všechny řádky.
Server: `Livewire::test()->html()`, medián z 5 běhů (100 000 řádků: 3 běhy), PHP 8.3 bez opcache.
Prohlížeč: Chromium, morph `<tbody>` přes Alpine morph z `livewire.js` (zná bloky markerů) s klíčem `wire:key`.
„HTML celkem“ obsahuje i Livewire snapshot (`public $dataset`), který tahle změna neřeší.
Výkyvy kolem ±10 % jsou šum měření (sdílený virtuální stroj), u 100 000 řádků proběhly jen 3 běhy.

## Server

| řádků | varianta | markery před → po | tbody před → po | HTML celkem před → po | render před → po | paměť před → po |
|---:|---|---:|---:|---:|---:|---:|
| 1 000 | bez akcí | 26 002 → 2 004 (-92 %) | 1.9 MB → 433 KB (-77 %) | 2.2 MB → 744 KB (-66 %) | 226 ms → 103 ms (-54 %) | 5.8 → 2.9 MB (-50 %) |
| 1 000 | akce | 39 202 → 10 404 (-73 %) | 4.2 MB → 2.6 MB (-37 %) | 5.1 MB → 3.5 MB (-31 %) | 329 ms → 195 ms (-41 %) | 17.6 → 14.5 MB (-18 %) |
| 1 000 | akce + výběr | 41 202 → 14 404 (-65 %) | 4.4 MB → 2.9 MB (-34 %) | 5.3 MB → 3.8 MB (-29 %) | 278 ms → 231 ms (-17 %) | 16.8 → 13.8 MB (-18 %) |
| 10 000 | bez akcí | 260 002 → 20 004 (-92 %) | 18.7 MB → 4.3 MB (-77 %) | 21.7 MB → 7.3 MB (-66 %) | 1.83 s → 1.04 s (-43 %) | 57.9 → 29.1 MB (-50 %) |
| 10 000 | akce | 392 002 → 104 004 (-73 %) | 42.0 MB → 26.3 MB (-37 %) | 50.7 MB → 35.0 MB (-31 %) | 3.38 s → 2.27 s (-33 %) | 162.8 → 131.4 MB (-19 %) |
| 10 000 | akce + výběr | 412 002 → 144 004 (-65 %) | 44.3 MB → 29.2 MB (-34 %) | 53.2 MB → 38.1 MB (-28 %) | 3.39 s → 2.52 s (-26 %) | 168.4 → 138.1 MB (-18 %) |
| 100 000 | bez akcí | 2 600 002 → 200 004 (-92 %) | 188.1 MB → 43.9 MB (-77 %) | 218.6 MB → 74.4 MB (-66 %) | 21.79 s → 10.51 s (-52 %) | 581.1 → 292.7 MB (-50 %) |
| 100 000 | akce | 3 920 002 → 1 040 004 (-73 %) | 420.8 MB → 263.8 MB (-37 %) | 508.8 MB → 351.8 MB (-31 %) | 42.01 s → 24.79 s (-41 %) | 1630.1 → 1316.1 MB (-19 %) |
| 100 000 | akce + výběr | 4 120 002 → 1 440 004 (-65 %) | 444.4 MB → 292.8 MB (-34 %) | 534.0 MB → 382.4 MB (-28 %) | 47.38 s → 43.58 s (-8 %) | 1685.6 → 1382.5 MB (-18 %) |

## Prohlížeč (morph)

| řádků | varianta | DOM uzlů před → po | morph 1 změněné buňky před → po | morph obráceného pořadí před → po |
|---:|---|---:|---:|---:|
| 1 000 | bez akcí | 79 671 → 26 675 (-67 %) | 273 ms → 149 ms (-46 %) | 264 ms → 155 ms (-41 %) |
| 1 000 | akce | 135 471 → 73 475 (-46 %) | 515 ms → 402 ms (-22 %) | 551 ms → 416 ms (-25 %) |
| 1 000 | akce + výběr | 145 471 → 86 475 (-41 %) | 629 ms → 443 ms (-29 %) | 588 ms → 456 ms (-22 %) |
| 10 000 | bez akcí | 796 671 → 266 675 (-67 %) | 4.81 s → 1.54 s (-68 %) | 3.77 s → 2.64 s (-30 %) |
| 10 000 | akce | **spadl** (nedostatek paměti) → 734 675 | **spadl** (nedostatek paměti) → 5.52 s | **spadl** (nedostatek paměti) → 6.66 s |
| 10 000 | akce + výběr | **spadl** (nedostatek paměti) → 864 675 | **spadl** (nedostatek paměti) → 6.09 s | **spadl** (nedostatek paměti) → 8.30 s |
