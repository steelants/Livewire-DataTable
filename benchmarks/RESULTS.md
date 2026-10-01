# Benchmark: před / po

Medián z 5 běhů (DB: 3 běhy), PHP 8.3 bez opcache, SQLite in-memory, 4 vCPU.
Řádek „10⁹ (odhad)“ je **extrapolace**, ne měření: filtrování/hledání lineárně (O(n)),
řazení jako O(n log n), proloženo dvěma největšími naměřenými velikostmi.
Opakované běhy stejného kódu se liší o 10–15 % (sdílený virtuální stroj) - rozdíly v tomto
rozsahu jsou šum. Střídavé A/B měření starého a nového kódu u DB driveru „bez filtru“,
„hledání“ a „řazení“ (identické SQL) ukázalo rozdíl mediánů do ±5 %.

## Driver: array

### bez filtru

| řádků | před | po | zrychlení | poznámka |
|---:|---:|---:|---:|---|
| 10 000 | 0.1 ms | 0.1 ms | 0.7× | dataset(): 5 → 2; headerFilters(): 2 → 1 |
| 100 000 | 0.1 ms | 0.1 ms | 0.8× | dataset(): 5 → 2; headerFilters(): 2 → 1 |
| 1 000 000 | 0.1 ms | 0.2 ms | 0.8× | dataset(): 5 → 2; headerFilters(): 2 → 1 |
| 10⁹ (odhad) | ~0.1 ms | ~0.2 ms | — | nezávisí na počtu řádků; rozdíl ~30 µs = režie once() |

### hledání

| řádků | před | po | zrychlení | poznámka |
|---:|---:|---:|---:|---|
| 10 000 | 6.8 ms | 6.8 ms | 1.0× | dataset(): 5 → 2; headerFilters(): 2 → 1 |
| 100 000 | 100.5 ms | 81.0 ms | 1.2× | dataset(): 5 → 2; headerFilters(): 2 → 1 |
| 1 000 000 | 1.64 s | 1.59 s | 1.0× | dataset(): 5 → 2; headerFilters(): 2 → 1 |
| 10⁹ (odhad) | 27.2 min | 26.3 min | 1.0× | extrapolace |

### filtry text+select+date

| řádků | před | po | zrychlení | poznámka |
|---:|---:|---:|---:|---|
| 10 000 | 7.6 ms | 4.1 ms | 1.9× | dataset(): 5 → 2; headerFilters(): 2 → 1 |
| 100 000 | 87.8 ms | 42.7 ms | 2.1× | dataset(): 5 → 2; headerFilters(): 2 → 1 |
| 1 000 000 | 971.6 ms | 777.9 ms | 1.2× | dataset(): 5 → 2; headerFilters(): 2 → 1 |
| 10⁹ (odhad) | 16.2 min | 12.9 min | 1.3× | extrapolace |

### řazení

| řádků | před | po | zrychlení | poznámka |
|---:|---:|---:|---:|---|
| 10 000 | 37.5 ms | 15.1 ms | 2.5× | dataset(): 5 → 2; headerFilters(): 2 → 1 |
| 100 000 | 572.0 ms | 196.3 ms | 2.9× | dataset(): 5 → 2; headerFilters(): 2 → 1 |
| 1 000 000 | 9.95 s | 2.76 s | 3.6× | dataset(): 5 → 2; headerFilters(): 2 → 1 |
| 10⁹ (odhad) | 4.1 h | 1.2 h | 3.6× | extrapolace |

### hledání+filtry+řazení

| řádků | před | po | zrychlení | poznámka |
|---:|---:|---:|---:|---|
| 10 000 | 17.4 ms | 8.4 ms | 2.1× | dataset(): 5 → 2; headerFilters(): 2 → 1 |
| 100 000 | 217.9 ms | 107.8 ms | 2.0× | dataset(): 5 → 2; headerFilters(): 2 → 1 |
| 1 000 000 | 3.41 s | 1.61 s | 2.1× | dataset(): 5 → 2; headerFilters(): 2 → 1 |
| 10⁹ (odhad) | 1.4 h | 40.1 min | 2.1× | extrapolace |

### selectAllFiltered (hledání)

| řádků | před | po | zrychlení | poznámka |
|---:|---:|---:|---:|---|
| 10 000 | 5.6 ms | 8.8 ms | 0.6× | vybráno: 10000 → 4079 |
| 100 000 | 62.2 ms | 101.7 ms | 0.6× | vybráno: 100000 → 40243 |
| 1 000 000 | 829.1 ms | 1.22 s | 0.7× | vybráno: 1000000 → 400258 |
| 10⁹ (odhad) | 13.8 min | 20.4 min | 0.7× | extrapolace |

## Driver: db

### bez filtru

| řádků | před | po | zrychlení | poznámka |
|---:|---:|---:|---:|---|
| 100 000 | 26.1 ms | 27.1 ms | 1.0× | headerFilters(): 1 → 1; SQL dotazů: 3 → 3 |
| 1 000 000 | 259.4 ms | 267.2 ms | 1.0× | headerFilters(): 1 → 1; SQL dotazů: 3 → 3 |
| 10 000 000 | 2.95 s | 2.84 s | 1.0× | headerFilters(): 1 → 1; SQL dotazů: 3 → 3 |
| 10⁹ (odhad) | 4.9 min | 4.7 min | 1.0× | extrapolace |

### hledání

| řádků | před | po | zrychlení | poznámka |
|---:|---:|---:|---:|---|
| 100 000 | 45.9 ms | 45.0 ms | 1.0× | headerFilters(): 1 → 1; SQL dotazů: 3 → 3 |
| 1 000 000 | 503.7 ms | 500.7 ms | 1.0× | headerFilters(): 1 → 1; SQL dotazů: 3 → 3 |
| 10 000 000 | 5.11 s | 5.04 s | 1.0× | headerFilters(): 1 → 1; SQL dotazů: 3 → 3 |
| 10⁹ (odhad) | 8.5 min | 8.4 min | 1.0× | extrapolace |

### filtry text+select

| řádků | před | po | zrychlení | poznámka |
|---:|---:|---:|---:|---|
| 100 000 | 90.0 ms | 38.8 ms | 2.3× | headerFilters(): 3 → 1; SQL dotazů: 5 → 3 |
| 1 000 000 | 888.1 ms | 400.9 ms | 2.2× | headerFilters(): 3 → 1; SQL dotazů: 5 → 3 |
| 10 000 000 | 9.41 s | 3.99 s | 2.4× | headerFilters(): 3 → 1; SQL dotazů: 5 → 3 |
| 10⁹ (odhad) | 15.7 min | 6.7 min | 2.4× | extrapolace |

### filtry text+select+date

| řádků | před | po | zrychlení | poznámka |
|---:|---:|---:|---:|---|
| 100 000 | — | 38.8 ms | — | před: **spadne** (ErrorException: Undefined array key "published_at.from"); headerFilters(): — → 1; SQL dotazů: — → 3 |
| 1 000 000 | — | 382.8 ms | — | před: **spadne** (ErrorException: Undefined array key "published_at.from"); headerFilters(): — → 1; SQL dotazů: — → 3 |
| 10 000 000 | — | 3.78 s | — | před: **spadne** (ErrorException: Undefined array key "published_at.from"); headerFilters(): — → 1; SQL dotazů: — → 3 |
| 10⁹ (odhad) | — | 6.3 min | — | extrapolace |

### řazení

| řádků | před | po | zrychlení | poznámka |
|---:|---:|---:|---:|---|
| 100 000 | 32.3 ms | 31.9 ms | 1.0× | headerFilters(): 1 → 1; SQL dotazů: 3 → 3 |
| 1 000 000 | 313.8 ms | 373.3 ms | 0.8× | headerFilters(): 1 → 1; SQL dotazů: 3 → 3 |
| 10 000 000 | 3.43 s | 3.31 s | 1.0× | headerFilters(): 1 → 1; SQL dotazů: 3 → 3 |
| 10⁹ (odhad) | 7.3 min | 7.1 min | 1.0× | extrapolace |

