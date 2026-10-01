// Browser morph benchmark: Livewire's bundled Alpine morph (the one that understands the
// [if BLOCK] markers), keyed by wire:key like Livewire does.
//
//   NODE_PATH=<dir with playwright> node benchmarks/morph-bench.cjs <html dir> <out.json>
const { chromium } = require('playwright');
const fs = require('node:fs');
const path = require('node:path');

(async () => {

const [dir, out] = process.argv.slice(2);
const livewire = fs.readFileSync(path.join(__dirname, '../vendor/livewire/livewire/dist/livewire.js'), 'utf8');
const browser = await chromium.launch({ executablePath: process.env.CHROMIUM || undefined });
const page = await browser.newPage();
await page.setContent('<html><body><table id="t"></table></body></html>');
await page.addScriptTag({ content: livewire });
// Livewire registers the morph plugin on start; injected after load, it has to be started by hand.
await page.evaluate(() => { if (!window.Alpine.morph) window.Livewire.start(); });

const sets = [...new Set(fs.readdirSync(dir).filter(f => f.endsWith('-a.html')).map(f => f.replace(/-a\.html$/, '')))];
const results = {};
for (const set of sets) {
    const read = s => fs.readFileSync(path.join(dir, `${set}-${s}.html`), 'utf8');
    const [a, b, c] = ['a', 'b', 'c'].map(read);
    results[set] = await page.evaluate(({ a, b, c }) => {
        const opts = { key: el => el.getAttribute && el.getAttribute('wire:key'), lookahead: false };
        const run = (from, to, runs) => {
            const times = [];
            for (let i = 0; i < runs; i++) {
                const table = document.getElementById('t');
                table.innerHTML = from;
                const tbody = table.querySelector('tbody');
                const t = performance.now();
                window.Alpine.morph(tbody, to, opts);
                times.push(performance.now() - t);
            }
            times.sort((x, y) => x - y);
            return Math.round(times[Math.floor(times.length / 2)] * 10) / 10;
        };
        const table = document.getElementById('t');
        table.innerHTML = a;
        const nodes = (() => {
            let n = 0, w = document.createTreeWalker(table, NodeFilter.SHOW_ALL);
            while (w.nextNode()) n++;
            return n;
        })();
        const comments = (() => {
            let n = 0, w = document.createTreeWalker(table, NodeFilter.SHOW_COMMENT);
            while (w.nextNode()) n++;
            return n;
        })();
        const runs = a.length > 5e6 ? 3 : 7;
        return { 'DOM uzlů': nodes, 'komentářů': comments, 'morph 1 buňka ms': run(a, b, runs), 'morph obrácené pořadí ms': run(a, c, runs) };
    }, { a, b, c });
    console.log(set, results[set]);
}
fs.writeFileSync(out, JSON.stringify(results, null, 2));
await browser.close();
})();
