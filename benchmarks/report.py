"""Builds benchmarks/RESULTS.md from the before/after JSON files."""
import json, math, os

D = os.path.dirname(os.path.abspath(__file__))
load = lambda f: json.load(open(os.path.join(D, f)))
before = {'array': load('before-array.json')['array'], 'db': load('before-db.json')['db']}
after = {'array': load('after-array.json')['array'], 'db': load('after-db.json')['db']}
TARGET = 10**9


def fmt_ms(ms):
    if ms is None:
        return '—'
    if ms >= 3600_000:
        return f'{ms / 3600_000:.1f} h'
    if ms >= 60_000:
        return f'{ms / 60_000:.1f} min'
    if ms >= 1000:
        return f'{ms / 1000:.2f} s'
    return f'{ms:.1f} ms'


def extrapolate(points, sort):
    """Least-squares fit of t = c * f(n) on the largest two sizes, f = n or n log n."""
    f = (lambda n: n * math.log(n)) if sort else (lambda n: n)
    pts = [(n, t) for n, t in points if t is not None and n > 0][-2:]
    if not pts:
        return None
    c = sum(t * f(n) for n, t in pts) / sum(f(n) ** 2 for n, _ in pts)
    return c * f(TARGET)


lines = ['# Benchmark: před / po', '',
         'Medián z 5 běhů (DB: 3 běhy), PHP 8.3 bez opcache, SQLite in-memory, 4 vCPU.',
         'Řádek „10⁹ (odhad)“ je **extrapolace**, ne měření: filtrování/hledání lineárně (O(n)),',
         'řazení jako O(n log n), proloženo dvěma největšími naměřenými velikostmi.', '']

for driver in ['array', 'db']:
    lines += [f'## Driver: {driver}', '']
    sizes = [n for n in before[driver] if n != '0']
    scenarios = list(after[driver][sizes[0]].keys())
    for sc in scenarios:
        sort = 'řazení' in sc
        lines += [f'### {sc}', '', '| řádků | před | po | zrychlení | poznámka |', '|---:|---:|---:|---:|---|']
        pb, pa = [], []
        for n in sizes:
            b = before[driver][n].get(sc, {})
            a = after[driver][n].get(sc, {})
            bm, am = b.get('ms'), a.get('ms')
            pb.append((int(n), bm)); pa.append((int(n), am))
            note = []
            if 'chyba' in b:
                note.append('před: **spadne** (' + b['chyba'][:60] + ')')
            for k in ['dataset()', 'headerFilters()', 'SQL dotazů', 'vybráno']:
                if k in b or k in a:
                    note.append(f'{k}: {b.get(k, "—")} → {a.get(k, "—")}')
            speed = f'{bm / am:.1f}×' if bm and am else '—'
            lines.append(f'| {int(n):,} | {fmt_ms(bm)} | {fmt_ms(am)} | {speed} | {"; ".join(note)} |'.replace(',', ' '))
        eb, ea = extrapolate(pb, sort), extrapolate(pa, sort)
        speed = f'{eb / ea:.1f}×' if eb and ea else '—'
        lines.append(f'| 10⁹ (odhad) | {fmt_ms(eb)} | {fmt_ms(ea)} | {speed} | extrapolace |')
        lines.append('')

open(os.path.join(D, 'RESULTS.md'), 'w').write('\n'.join(lines) + '\n')
print('\n'.join(lines))
