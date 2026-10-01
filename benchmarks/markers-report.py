"""Builds benchmarks/MARKERS.md from markers-{before,after}.json and morph-{before,after}.json."""
import json, os

D = os.path.dirname(os.path.abspath(__file__))
load = lambda f: json.load(open(os.path.join(D, f)))
mb, ma = load('markers-before.json'), load('markers-after.json')
pb, pa = load('morph-before.json'), load('morph-after.json')


def pct(b, a):
    if not b or a is None:
        return '—'
    return f'{(a - b) / b * 100:+.0f} %'


def size(v):
    return f'{v / 1048576:.1f} MB' if v >= 1048576 else f'{v / 1024:.0f} KB'


def ms(v):
    return f'{v / 1000:.2f} s' if v >= 1000 else f'{v:.0f} ms'


lines = [
    '# Benchmark: morph markery v tbody',
    '',
    'Tabulka s 8 sloupci: 4 plain, 2 render casty, 2× `renderColumnX()` (jeden vrací Blade komponentu',
    's vlastním `@if`, podobně jako `x-badge` v appce). Varianta „akce“ má 3 akce na řádek (každý pátý',
    'řádek žádnou), „akce + výběr“ navíc `HasBulkActions`. Bez stránkování, renderují se všechny řádky.',
    'Server: `Livewire::test()->html()`, medián z 5 běhů (100 000 řádků: 3 běhy), PHP 8.3 bez opcache.',
    'Prohlížeč: Chromium, morph `<tbody>` přes Alpine morph z `livewire.js` (zná bloky markerů) s klíčem `wire:key`.',
    '„HTML celkem“ obsahuje i Livewire snapshot (`public $dataset`), který tahle změna neřeší.',
    'Výkyvy kolem ±10 % jsou šum měření (sdílený virtuální stroj), u 100 000 řádků proběhly jen 3 běhy.',
    '',
    '## Server',
    '',
    '| řádků | varianta | markery před → po | tbody před → po | HTML celkem před → po | render před → po | paměť před → po |',
    '|---:|---|---:|---:|---:|---:|---:|',
]
for n in mb:
    for v in mb[n]:
        b, a = mb[n][v], ma[n][v]
        lines.append(
            f"| {int(n):,} | {v} | {b['markers']:,} → {a['markers']:,} ({pct(b['markers'], a['markers'])}) "
            f"| {size(b['tbody B'])} → {size(a['tbody B'])} ({pct(b['tbody B'], a['tbody B'])}) "
            f"| {size(b['html B'])} → {size(a['html B'])} ({pct(b['html B'], a['html B'])}) "
            f"| {ms(b['ms'])} → {ms(a['ms'])} ({pct(b['ms'], a['ms'])}) "
            f"| {b['peak MB']} → {a['peak MB']} MB ({pct(b['peak MB'], a['peak MB'])}) |".replace(',', ' ')
        )

lines += ['', '## Prohlížeč (morph)', '',
          '| řádků | varianta | DOM uzlů před → po | morph 1 změněné buňky před → po | morph obráceného pořadí před → po |',
          '|---:|---|---:|---:|---:|']
names = {}
for n in mb:
    for v in mb[n]:
        f = mb[n][v].get('html files')
        if f:
            names[f] = (n, v)
for key, (n, v) in names.items():
    b, a = pb.get(key, {}), pa.get(key, {})
    if 'chyba' in b or 'chyba' in a:
        side = lambda r: '**spadl** (nedostatek paměti)' if 'chyba' in r else None
        cell = lambda k: f"{side(b) or ms(b[k])} → {side(a) or ms(a[k])}"
        nodes = f"{side(b) or format(b['DOM uzlů'], ',')} → {side(a) or format(a['DOM uzlů'], ',')}"
        lines.append(f"| {int(n):,} | {v} | {nodes} | {cell('morph 1 buňka ms')} | {cell('morph obrácené pořadí ms')} |".replace(',', ' '))
        continue
    lines.append(
        f"| {int(n):,} | {v} | {b['DOM uzlů']:,} → {a['DOM uzlů']:,} ({pct(b['DOM uzlů'], a['DOM uzlů'])}) "
        f"| {ms(b['morph 1 buňka ms'])} → {ms(a['morph 1 buňka ms'])} ({pct(b['morph 1 buňka ms'], a['morph 1 buňka ms'])}) "
        f"| {ms(b['morph obrácené pořadí ms'])} → {ms(a['morph obrácené pořadí ms'])} ({pct(b['morph obrácené pořadí ms'], a['morph obrácené pořadí ms'])}) |".replace(',', ' ')
    )

open(os.path.join(D, 'MARKERS.md'), 'w').write('\n'.join(lines) + '\n')
print('\n'.join(lines))
