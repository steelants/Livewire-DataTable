<?php

/**
 * Benchmark of the data pipeline for both drivers.
 *
 * Not part of the test suite. Run it with:
 *   BENCH_SIZES_ARRAY=10000,100000,1000000 BENCH_SIZES_DB=100000,1000000 \
 *   (an empty BENCH_SIZES_* skips that driver)
 *   BENCH_OUT=benchmarks/out.json vendor/bin/pest benchmarks/BenchTest.php
 */

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use SteelAnts\DataTable\Livewire\DataTableComponent;
use SteelAnts\DataTable\Traits\HasBulkActions;
use SteelAnts\DataTable\Traits\UseDatabase;
use Tests\Fixtures\Post;

uses(Tests\TestCase::class);

class BenchArrayTable extends DataTableComponent
{
    use HasBulkActions;

    public static array $rows = [];
    public static int $datasetCalls = 0;
    public static int $headerFilterCalls = 0;

    public bool $searchable = true;
    public bool $filterable = true;
    public int $itemsPerPage = 10;

    public function dataset(): array
    {
        static::$datasetCalls++;

        return static::$rows;
    }

    public function headerFilters(): array
    {
        static::$headerFilterCalls++;

        return [
            'name' => ['type' => 'text'],
            'city' => ['type' => 'select', 'values' => ['Praha' => 'Praha', 'Brno' => 'Brno']],
            'date' => ['type' => 'date'],
        ];
    }

    public function selectAllAcrossPages(): bool
    {
        return true;
    }
}

class BenchDbTable extends DataTableComponent
{
    use UseDatabase;

    public static int $headerFilterCalls = 0;

    public bool $searchable = true;
    public bool $filterable = true;
    public int $itemsPerPage = 10;

    public function query(): Builder
    {
        return Post::query();
    }

    public function headers(): array
    {
        return ['id' => 'ID', 'title' => 'Title', 'score' => 'Score', 'published_at' => 'Published'];
    }

    public function headerFilters(): array
    {
        static::$headerFilterCalls++;

        // Typical real-world definition: select options loaded from the database.
        return [
            'title'        => ['type' => 'text'],
            'score'        => ['type' => 'select', 'values' => DB::table('posts')->distinct()->orderBy('score')->limit(50)->pluck('score', 'score')->all()],
            'published_at' => ['type' => 'date'],
        ];
    }
}

function benchMeasure(callable $fn, int $runs): array
{
    $times = [];
    for ($i = 0; $i < $runs; $i++) {
        gc_collect_cycles();
        $t = hrtime(true);
        $fn();
        $times[] = (hrtime(true) - $t) / 1e6;
    }
    sort($times);

    return ['ms' => round($times[intdiv(count($times), 2)], 2)];
}

function benchSizes(string $env, string $default): array
{
    $value = getenv($env);
    $value = $value === false ? $default : $value;

    // Empty = skip this driver.
    return $value === '' ? [] : array_map('intval', explode(',', $value));
}

it('benchmarks', function () {
    $runs = (int) (getenv('BENCH_RUNS') ?: 5);
    $results = [];
    $names = ['Jan Novák', 'Petr Svoboda', 'Jana Nováková', 'Karel Dvořák', 'Eva Malá'];
    $cities = ['Praha', 'Brno', 'Ostrava'];

    $arrayScenarios = [
        'bez filtru'              => [],
        'hledání'                 => ['searchValue' => 'nová'],
        'filtry text+select+date' => ['headerFilter' => ['name' => 'nov', 'city' => 'Praha', 'date' => ['from' => '2021-01-01', 'to' => '2023-12-31']]],
        'řazení'                  => ['sortBy' => 'score'],
        'hledání+filtry+řazení'   => ['searchValue' => 'a', 'headerFilter' => ['city' => 'Brno', 'date' => ['from' => '2021-01-01']], 'sortBy' => 'name'],
    ];

    foreach (benchSizes('BENCH_SIZES_ARRAY', '10000,100000') as $n) {
        mt_srand(1);
        $rows = [];
        for ($i = 1; $i <= $n; $i++) {
            $rows[] = [
                'id'    => $i,
                'name'  => $names[mt_rand(0, 4)] . ' ' . $i,
                'score' => mt_rand(0, 1000),
                'city'  => $cities[mt_rand(0, 2)],
                'date'  => date('Y-m-d', 1577836800 + mt_rand(0, 5 * 365) * 86400),
            ];
        }
        BenchArrayTable::$rows = $rows;
        unset($rows);

        foreach ($arrayScenarios as $label => $props) {
            BenchArrayTable::$datasetCalls = BenchArrayTable::$headerFilterCalls = 0;
            $r = benchMeasure(function () use ($props) {
                $t = new BenchArrayTable();
                $t->bootHasBulkActions();
                foreach ($props as $k => $v) {
                    $t->{$k} = $v;
                }
                $t->render();
            }, $runs);
            $r['dataset()'] = BenchArrayTable::$datasetCalls / $runs;
            $r['headerFilters()'] = BenchArrayTable::$headerFilterCalls / $runs;
            $results['array'][$n][$label] = $r;
        }

        $r = benchMeasure(function () {
            $t = new BenchArrayTable();
            $t->bootHasBulkActions();
            $t->searchValue = 'nová';
            $t->selectAllFiltered();
            $GLOBALS['benchSelected'] = count($t->selected);
        }, $runs);
        $r['vybráno'] = $GLOBALS['benchSelected'];
        $results['array'][$n]['selectAllFiltered (hledání)'] = $r;
    }
    BenchArrayTable::$rows = [];

    $dbScenarios = [
        'bez filtru'              => [],
        'hledání'                 => ['searchValue' => 'nová'],
        'filtry text+select'      => ['headerFilter' => ['title' => 'nov', 'score' => '500']],
        'filtry text+select+date' => ['headerFilter' => ['title' => 'nov', 'score' => '500', 'published_at' => ['from' => '2021-01-01', 'to' => '2023-12-31']]],
        'řazení'                  => ['sortBy' => 'score'],
    ];

    foreach (benchSizes('BENCH_SIZES_DB', '100000') as $n) {
        DB::table('posts')->truncate();
        mt_srand(1);
        DB::transaction(function () use ($n, $names) {
            $batch = [];
            for ($i = 1; $i <= $n; $i++) {
                $batch[] = [
                    'title'        => $names[mt_rand(0, 4)] . ' ' . $i,
                    'score'        => mt_rand(0, 1000),
                    'published'    => true,
                    'published_at' => date('Y-m-d', 1577836800 + mt_rand(0, 5 * 365) * 86400),
                ];
                if (count($batch) === 2000) {
                    DB::table('posts')->insert($batch);
                    $batch = [];
                }
            }
            if ($batch) {
                DB::table('posts')->insert($batch);
            }
        });

        foreach ($dbScenarios as $label => $props) {
            $queries = 0;
            BenchDbTable::$headerFilterCalls = 0;
            DB::listen(function () use (&$queries) {
                $queries++;
            });
            try {
                $r = benchMeasure(function () use ($props) {
                    $t = new BenchDbTable();
                    foreach ($props as $k => $v) {
                        $t->{$k} = $v;
                    }
                    $t->render();
                }, $runs);
                $r['SQL dotazů'] = $queries / $runs;
                $r['headerFilters()'] = BenchDbTable::$headerFilterCalls / $runs;
            } catch (Throwable $e) {
                $r = ['chyba' => class_basename($e) . ': ' . $e->getMessage()];
            }
            DB::getEventDispatcher()->forget(Illuminate\Database\Events\QueryExecuted::class);
            $results['db'][$n][$label] = $r;
        }
    }

    file_put_contents(getenv('BENCH_OUT') ?: 'benchmarks/out.json', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    expect(true)->toBeTrue();
});
