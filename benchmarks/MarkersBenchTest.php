<?php

/**
 * Morph markers, HTML size and render time of the table body.
 *
 * Not part of the test suite. Run it with:
 *   BENCH_ROWS=1000,10000,100000 BENCH_OUT=benchmarks/markers-after.json \
 *   BENCH_HTML_DIR=/tmp/markers vendor/bin/pest benchmarks/MarkersBenchTest.php
 *
 * BENCH_HTML_DIR (optional) receives the <tbody> of two renders (sorted asc, and asc with
 * one changed cell) for the browser morph benchmark in benchmarks/morph-bench.cjs.
 */

use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Tests\Fixtures\BenchBadge;
use Tests\Fixtures\MarkersActionsComponent;
use Tests\Fixtures\MarkersSelectableComponent;
use Tests\Fixtures\MarkersTableComponent;

uses(Tests\TestCase::class);

it('benchmarks morph markers', function () {
    Blade::component('bench-badge', BenchBadge::class);

    $sizes = array_map('intval', explode(',', getenv('BENCH_ROWS') ?: '1000,10000'));
    $htmlDir = getenv('BENCH_HTML_DIR') ?: null;
    $variants = [
        'bez akcí'       => MarkersTableComponent::class,
        'akce'           => MarkersActionsComponent::class,
        'akce + výběr'   => MarkersSelectableComponent::class,
    ];
    $results = [];

    foreach ($sizes as $n) {
        $rows = MarkersTableComponent::rows($n);
        $runs = (int) (getenv('BENCH_RUNS') ?: ($n >= 100000 ? 3 : 5));

        foreach ($variants as $label => $class) {
            MarkersTableComponent::$rows = $rows;
            $times = [];
            $html = '';
            for ($i = 0; $i < $runs; $i++) {
                gc_collect_cycles();
                memory_reset_peak_usage();
                $before = memory_get_usage();
                $t = hrtime(true);
                $html = Livewire::test($class)->html();
                $times[] = (hrtime(true) - $t) / 1e6;
                $peak = memory_get_peak_usage() - $before;
            }
            sort($times);
            preg_match('/<tbody>.*<\/tbody>/s', $html, $tbody);

            $results[$n][$label] = [
                'ms'       => round($times[intdiv(count($times), 2)], 1),
                'markers'  => morphMarkerCount($tbody[0]),
                'tbody B'  => strlen($tbody[0]),
                'html B'   => strlen($html),
                'peak MB'  => round($peak / 1048576, 1),
            ];

            if ($htmlDir && $n <= 10000) {
                @mkdir($htmlDir, 0777, true);
                $slug = substr(md5($label), 0, 6);
                file_put_contents("{$htmlDir}/{$n}-{$slug}-a.html", $tbody[0]);

                // Same table with one changed cell - a typical re-render (inline edit, toggle).
                $changed = $rows;
                $changed[intdiv($n, 2)]['status'] = 'done';
                $changed[intdiv($n, 2)]['title'] = 'Edited';
                MarkersTableComponent::$rows = $changed;
                preg_match('/<tbody>.*<\/tbody>/s', Livewire::test($class)->html(), $b);
                file_put_contents("{$htmlDir}/{$n}-{$slug}-b.html", $b[0]);

                // Sorted the other way - every row moves (keyed morph).
                MarkersTableComponent::$rows = array_reverse($rows);
                preg_match('/<tbody>.*<\/tbody>/s', Livewire::test($class)->html(), $c);
                file_put_contents("{$htmlDir}/{$n}-{$slug}-c.html", $c[0]);
                $results[$n][$label]['html files'] = "{$n}-{$slug}";
            }
        }
    }
    MarkersTableComponent::$rows = [];

    file_put_contents(getenv('BENCH_OUT') ?: 'benchmarks/markers.json', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    expect(true)->toBeTrue();
});
