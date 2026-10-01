<?php

use Tests\Fixtures\ArrayTableComponent;
use Tests\Fixtures\ArrayTableWithRowComponent;
use Tests\Fixtures\Post;
use Tests\Fixtures\PostTableComponent;

function arrayTable(array $props = [], string $class = ArrayTableComponent::class): ArrayTableComponent
{
    $table = new $class();
    $table->bootHasBulkActions();
    foreach ($props as $name => $value) {
        $table->{$name} = $value;
    }

    return $table;
}

function postTable(array $props = []): PostTableComponent
{
    $table = new PostTableComponent();
    foreach ($props as $name => $value) {
        $table->{$name} = $value;
    }

    return $table;
}

function arrayRows(): array
{
    return [
        ['id' => 1, 'name' => 'Jan Novák', 'score' => 30, 'city' => 'Praha', 'date' => '2024-01-10'],
        ['id' => 2, 'name' => 'Petr Svoboda', 'score' => 10, 'city' => 'Brno', 'date' => '2024-02-15'],
        ['id' => 3, 'name' => 'Jana Nováková', 'score' => 20, 'city' => 'Praha', 'date' => '2024-03-20'],
        ['id' => 4, 'name' => 'Karel Dvořák', 'score' => 10, 'city' => 'Brno', 'date' => '2024-04-25'],
        ['id' => 5, 'name' => '100% hotovo', 'score' => 5, 'city' => 'Praha', 'date' => 'not a date'],
    ];
}


beforeEach(function () {
    ArrayTableComponent::resetFixture(arrayRows());
    PostTableComponent::$calls = ['headerFilters' => 0];
});

// ─── Array driver ─────────────────────────────────────────────────────────────

describe('array driver search', function () {
    it('returns everything without a search value', function () {
        expect(array_column(tableData(arrayTable()), 'id'))->toBe([1, 2, 3, 4, 5]);
    });

    it('matches case-insensitively, including multibyte characters', function () {
        $table = arrayTable(['searchValue' => 'NOVÁK']);

        expect(array_column(tableData($table), 'id'))->toBe([1, 3])
            ->and($table->itemsTotal)->toBe(2);
    });

    it('ignores leading and trailing spaces', function () {
        expect(array_column(tableData(arrayTable(['searchValue' => '  novák  '])), 'id'))->toBe([1, 3]);
    });

    it('treats a whitespace-only value as no search', function () {
        expect(array_column(tableData(arrayTable(['searchValue' => '   '])), 'id'))->toBe([1, 2, 3, 4, 5]);
    });

    it('returns nothing when no row matches', function () {
        $table = arrayTable(['searchValue' => 'xyz']);

        expect(tableData($table))->toBe([])
            ->and($table->itemsTotal)->toBe(0);
    });

    it('searches only the searchable columns', function () {
        $table = arrayTable(['searchValue' => 'Brno', 'searchableColumns' => ['name']]);

        expect(tableData($table))->toBe([]);
    });
});

describe('array driver header filters', function () {
    it('filters by text', function () {
        expect(array_column(tableData(arrayTable(['headerFilter' => ['name' => 'nov']])), 'id'))->toBe([1, 3]);
    });

    it('trims the text filter', function () {
        expect(array_column(tableData(arrayTable(['headerFilter' => ['name' => ' nov ']])), 'id'))->toBe([1, 3]);
    });

    it('ignores an empty text filter', function () {
        expect(array_column(tableData(arrayTable(['headerFilter' => ['name' => '']])), 'id'))->toBe([1, 2, 3, 4, 5]);
    });

    it('filters by select', function () {
        expect(array_column(tableData(arrayTable(['headerFilter' => ['city' => 'Brno']])), 'id'))->toBe([2, 4]);
    });

    it('filters by date from', function () {
        // An unparseable row date is never excluded by a date filter.
        expect(array_column(tableData(arrayTable(['headerFilter' => ['date' => ['from' => '2024-02-01']]])), 'id'))
            ->toBe([2, 3, 4, 5]);
    });

    it('filters by date to', function () {
        expect(array_column(tableData(arrayTable(['headerFilter' => ['date' => ['to' => '2024-02-15']]])), 'id'))
            ->toBe([1, 2, 5]);
    });

    it('filters by date range', function () {
        $filter = ['date' => ['from' => '2024-02-01', 'to' => '2024-03-31']];

        expect(array_column(tableData(arrayTable(['headerFilter' => $filter])), 'id'))->toBe([2, 3, 5]);
    });

    it('ignores an empty date range', function () {
        $filter = ['date' => ['from' => '', 'to' => '']];

        expect(array_column(tableData(arrayTable(['headerFilter' => $filter])), 'id'))->toBe([1, 2, 3, 4, 5]);
    });

    it('ignores a filter on a column the rows do not have', function () {
        expect(array_column(tableData(arrayTable(['headerFilter' => ['missing' => 'x']])), 'id'))->toBe([1, 2, 3, 4, 5]);
    });

    it('combines filters with search', function () {
        $table = arrayTable(['headerFilter' => ['city' => 'Praha'], 'searchValue' => 'jan']);

        expect(array_column(tableData($table), 'id'))->toBe([1, 3]);
    });

    it('does not filter when filtering is disabled', function () {
        $table = arrayTable(['filterable' => false, 'headerFilter' => ['city' => 'Brno']]);

        expect(array_column(tableData($table), 'id'))->toBe([1, 2, 3, 4, 5]);
    });
});

describe('array driver sorting', function () {
    it('sorts numbers ascending and keeps equal values in original order', function () {
        expect(array_column(tableData(arrayTable(['sortBy' => 'score'])), 'id'))->toBe([5, 2, 4, 3, 1]);
    });

    it('sorts numbers descending and keeps equal values in original order', function () {
        expect(array_column(tableData(arrayTable(['sortBy' => 'score', 'sortDirection' => 'desc'])), 'id'))
            ->toBe([1, 3, 2, 4, 5]);
    });

    it('sorts strings byte-wise', function () {
        expect(array_column(tableData(arrayTable(['sortBy' => 'name'])), 'id'))->toBe([5, 1, 3, 4, 2]);
    });

    it('sorts mixed numeric and non-numeric values', function () {
        ArrayTableComponent::$rows = [
            ['id' => 1, 'name' => 'b', 'score' => 'b', 'city' => '', 'date' => ''],
            ['id' => 2, 'name' => 'a', 'score' => 10, 'city' => '', 'date' => ''],
            ['id' => 3, 'name' => 'c', 'score' => null, 'city' => '', 'date' => ''],
            ['id' => 4, 'name' => 'd', 'score' => 9, 'city' => '', 'date' => ''],
            ['id' => 5, 'name' => 'e', 'score' => '9.5', 'city' => '', 'date' => ''],
        ];

        expect(array_column(tableData(arrayTable(['sortBy' => 'score'])), 'id'))
            ->toBe(array_column(referenceSort(ArrayTableComponent::$rows, 'score', 1), 'id'));
    });

    it('sorts by the value returned from row() and a dotted key', function () {
        $table = arrayTable(['sortBy' => 'meta.rank'], ArrayTableWithRowComponent::class);

        // rank = 100 - score, so ascending rank = descending score.
        expect(array_column(tableData($table), 'id'))->toBe([1, 3, 2, 4, 5]);
    });

    it('sorts by a missing key without failing', function () {
        expect(array_column(tableData(arrayTable(['sortBy' => 'nope'])), 'id'))->toBe([1, 2, 3, 4, 5]);
    });

    it('does not sort when sorting is disabled', function () {
        expect(array_column(tableData(arrayTable(['sortable' => false, 'sortBy' => 'score'])), 'id'))->toBe([1, 2, 3, 4, 5]);
    });
});

/**
 * Copy of the comparison used by DataTableComponent before the sort optimisation -
 * the reference the optimised implementation must match.
 */
function referenceSort(array $rows, string $key, int $dir): array
{
    usort($rows, function ($a, $b) use ($key, $dir) {
        $av = $a[$key] ?? null;
        $bv = $b[$key] ?? null;
        $cmp = is_numeric($av) && is_numeric($bv) ? (float) $av <=> (float) $bv : strcmp((string) $av, (string) $bv);

        return $cmp * $dir;
    });

    return $rows;
}

describe('array driver sorting matches the reference on random data', function () {
    it('matches for direction', function (int $dir) {
        mt_srand(42);
        $rows = [];
        $pool = [null, '', 'a', 'B', 'ž', '10', '9', '1e3', '0x1A', ' 5', '5 ', 3, 3.0, -1, '-1.5'];
        for ($i = 1; $i <= 300; $i++) {
            $rows[] = ['id' => $i, 'name' => 'n', 'score' => $pool[mt_rand(0, count($pool) - 1)], 'city' => '', 'date' => ''];
        }
        ArrayTableComponent::$rows = $rows;

        $table = arrayTable(['sortBy' => 'score', 'sortDirection' => $dir === 1 ? 'asc' : 'desc']);

        expect(array_column(tableData($table), 'id'))->toBe(array_column(referenceSort($rows, 'score', $dir), 'id'));
    })->with([1, -1]);
});

describe('array driver pagination and transformations', function () {
    it('paginates after filtering and reports totals', function () {
        $table = arrayTable(['paginated' => true, 'itemsPerPage' => 2, 'currentPage' => 2, 'headerFilter' => ['city' => 'Praha']]);

        expect(array_column(tableData($table), 'id'))->toBe([5])
            ->and($table->itemsTotal)->toBe(3)
            ->and($table->pagesTotal)->toBe(2);
    });

    it('applies row() and column transformations', function () {
        $table = arrayTable(['paginated' => true, 'itemsPerPage' => 2], ArrayTableWithRowComponent::class);

        expect(tableData($table))->toBe([
            ['id' => 1, 'name' => 'JAN NOVÁK', 'score' => 30, 'meta' => ['rank' => 70]],
            ['id' => 2, 'name' => 'PETR SVOBODA', 'score' => 10, 'meta' => ['rank' => 90]],
        ]);
    });

    it('filters on the original values, before row()', function () {
        $table = arrayTable(['searchValue' => 'jan novák'], ArrayTableWithRowComponent::class);

        expect(array_column(tableData($table), 'name'))->toBe(['JAN NOVÁK']);
    });
});

describe('array driver select all filtered', function () {
    it('selects every row without filters', function () {
        $table = arrayTable();
        tableData($table);

        expect($table->selectAllFiltered())->toBeTrue()
            ->and($table->selected)->toBe(['1', '2', '3', '4', '5']);
    });

    it('respects the search value', function () {
        $table = arrayTable(['searchValue' => 'novák']);
        tableData($table);
        $table->selectAllFiltered();

        expect($table->selected)->toBe(['1', '3']);
    });

    it('respects header filters', function () {
        $table = arrayTable(['headerFilter' => ['city' => 'Brno']]);
        tableData($table);
        $table->selectAllFiltered();

        expect($table->selected)->toBe(['2', '4']);
    });
});

// ─── Database driver ──────────────────────────────────────────────────────────

describe('database driver search', function () {
    beforeEach(fn () => seedPosts());

    it('matches a term', function () {
        $table = postTable(['searchValue' => 'Svoboda']);

        expect(array_column(tableData($table), 'title'))->toBe(['Petr Svoboda'])
            ->and($table->itemsTotal)->toBe(1);
    });

    it('ignores leading and trailing spaces', function () {
        expect(array_column(tableData(postTable(['searchValue' => '  Svoboda '])), 'title'))->toBe(['Petr Svoboda']);
    });

    it('treats a whitespace-only value as no search', function () {
        expect(tableData(postTable(['searchValue' => '  '])))->toHaveCount(8);
    });

    it('supports * as a wildcard', function () {
        expect(array_column(tableData(postTable(['searchValue' => 'Jan*ková'])), 'title'))->toBe(['Jana Nováková']);
    });

    it('treats % in the search value literally', function () {
        expect(array_column(tableData(postTable(['searchValue' => '100%'])), 'title'))->toBe(['100% hotovo']);
    });

    it('treats _ in the search value literally', function () {
        expect(array_column(tableData(postTable(['searchValue' => 'a_b'])), 'title'))->toBe(['a_b']);
    });
});

describe('database driver header filters', function () {
    beforeEach(fn () => seedPosts());

    it('filters by text', function () {
        expect(array_column(tableData(postTable(['headerFilter' => ['title' => 'Svob']])), 'title'))->toBe(['Petr Svoboda']);
    });

    it('trims the text filter', function () {
        expect(array_column(tableData(postTable(['headerFilter' => ['title' => ' Svob ']])), 'title'))->toBe(['Petr Svoboda']);
    });

    it('treats % in the text filter literally', function () {
        expect(array_column(tableData(postTable(['headerFilter' => ['title' => '100%']])), 'title'))->toBe(['100% hotovo']);
    });

    it('filters by select', function () {
        expect(array_column(tableData(postTable(['headerFilter' => ['score' => '10']])), 'title'))
            ->toBe(['Petr Svoboda', 'Karel Dvořák']);
    });

    it('filters by date range', function () {
        $filter = ['published_at' => ['from' => '2024-02-01', 'to' => '2024-03-31']];

        expect(array_column(tableData(postTable(['headerFilter' => $filter])), 'title'))
            ->toBe(['Petr Svoboda', 'Jana Nováková']);
    });

    it('filters by date from only', function () {
        $filter = ['published_at' => ['from' => '2024-03-01', 'to' => '']];

        expect(array_column(tableData(postTable(['headerFilter' => $filter])), 'title'))
            ->toBe(['Jana Nováková', 'Karel Dvořák']);
    });

    it('combines search and filters', function () {
        $table = postTable(['searchValue' => 'nov', 'headerFilter' => ['score' => '20']]);

        expect(array_column(tableData($table), 'title'))->toBe(['Jana Nováková']);
    });
});

// ─── Call counts ──────────────────────────────────────────────────────────────

describe('expensive callbacks', function () {
    it('builds headers and header filters once per render in the array driver', function () {
        $table = arrayTable(['headerFilter' => ['city' => 'Praha', 'name' => 'a']]);
        $table->render();

        // dataset() once for the rows, once for headers().
        expect(ArrayTableComponent::$calls)->toBe(['dataset' => 2, 'headerFilters' => 1]);
    });

    it('builds header filters once per render in the database driver', function () {
        seedPosts();
        $table = postTable(['headerFilter' => ['title' => 'a', 'score' => '10']]);
        $table->render();

        expect(PostTableComponent::$calls['headerFilters'])->toBe(1);
    });
});

// ─── Database driver: relations, pagination, row(), select all ────────────────

function seedComments(): void
{
    Post::insert([
        ['title' => '100% done', 'score' => 10, 'published' => true],
        ['title' => '100 done', 'score' => 20, 'published' => true],
        ['title' => 'Svoboda', 'score' => 10, 'published' => true],
    ]);
    Tests\Fixtures\Comment::insert([
        ['post_id' => 1, 'body' => 'first'],
        ['post_id' => 2, 'body' => 'second'],
        ['post_id' => 3, 'body' => 'third'],
        ['post_id' => 99, 'body' => 'orphan'],
    ]);
}

function commentTable(array $props = []): Tests\Fixtures\CommentTableComponent
{
    $table = new Tests\Fixtures\CommentTableComponent();
    foreach ($props as $name => $value) {
        $table->{$name} = $value;
    }

    return $table;
}

describe('database driver relation columns', function () {
    beforeEach(fn () => seedComments());

    it('renders relation values through the join', function () {
        expect(array_column(tableData(commentTable()), 'post.title'))->toBe(['100% done', '100 done', 'Svoboda', null]);
    });

    it('searches a relation column', function () {
        expect(array_column(tableData(commentTable(['searchValue' => 'svob'])), 'body'))->toBe(['third']);
    });

    it('searches local and relation columns together', function () {
        expect(array_column(tableData(commentTable(['searchValue' => 'second'])), 'body'))->toBe(['second']);
    });

    it('treats % literally in a relation search', function () {
        expect(array_column(tableData(commentTable(['searchValue' => '100%'])), 'body'))->toBe(['first']);
    });

    it('supports * as a wildcard in a relation search', function () {
        expect(array_column(tableData(commentTable(['searchValue' => '100*done'])), 'body'))->toBe(['first', 'second']);
    });

    it('filters a relation column by text, nested the way Livewire sends it', function () {
        $table = commentTable(['headerFilter' => ['post' => ['title' => ' 100% ']]]);

        expect(array_column(tableData($table), 'body'))->toBe(['first']);
    });

    it('filters a relation column by select', function () {
        $table = commentTable(['headerFilter' => ['post' => ['score' => '10']]]);

        expect(array_column(tableData($table), 'body'))->toBe(['first', 'third']);
    });

    it('combines a local and a relation filter', function () {
        $table = commentTable(['headerFilter' => ['body' => 'ir', 'post' => ['score' => '10']]]);

        expect(array_column(tableData($table), 'body'))->toBe(['first', 'third']);
    });
});

describe('database driver pagination', function () {
    beforeEach(fn () => seedPosts());

    it('returns the requested page and totals', function () {
        $table = postTable(['paginated' => true, 'itemsPerPage' => 3, 'currentPage' => 2]);

        expect(array_column(tableData($table), 'id'))->toBe([4, 5, 6])
            ->and($table->itemsTotal)->toBe(8)
            ->and($table->pagesTotal)->toBe(3);
    });

    it('returns a partial last page', function () {
        $table = postTable(['paginated' => true, 'itemsPerPage' => 3, 'currentPage' => 3]);

        expect(array_column(tableData($table), 'id'))->toBe([7, 8]);
    });

    it('counts the filtered rows, not the page', function () {
        $table = postTable(['paginated' => true, 'itemsPerPage' => 1, 'searchValue' => 'hotovo']);

        expect(array_column(tableData($table), 'id'))->toBe([5])
            ->and($table->itemsTotal)->toBe(2)
            ->and($table->pagesTotal)->toBe(2);
    });

    it('keeps rows with equal sort values in a stable order across pages', function () {
        $ids = [];
        foreach ([1, 2, 3, 4] as $page) {
            $table = postTable(['paginated' => true, 'itemsPerPage' => 2, 'currentPage' => $page, 'sortBy' => 'score']);
            $ids = array_merge($ids, array_column(tableData($table), 'id'));
        }

        // Every row exactly once; ties (score 1, 5, 10) ordered by id.
        expect($ids)->toBe([7, 8, 5, 6, 2, 4, 3, 1]);
    });
});

describe('database driver row transformations', function () {
    beforeEach(fn () => seedPosts());

    it('applies row() and passes the model attribute to column methods', function () {
        $table = new Tests\Fixtures\PostRowComponent();
        $table->paginated = true;
        $table->itemsPerPage = 2;

        expect(tableData($table))->toBe([
            ['id' => 1, 'title' => 'JAN NOVÁK', 'score' => 300],
            ['id' => 2, 'title' => 'PETR SVOBODA', 'score' => 100],
        ]);
    });
});

describe('database driver select all filtered', function () {
    beforeEach(fn () => seedPosts());

    function postBulkTable(array $props): Tests\Fixtures\PostBulkTableComponent
    {
        $table = new Tests\Fixtures\PostBulkTableComponent();
        $table->bootHasBulkActions();
        foreach ($props as $name => $value) {
            $table->{$name} = $value;
        }

        return $table;
    }

    it('respects the search value', function () {
        $table = postBulkTable(['paginated' => true, 'itemsPerPage' => 1, 'searchValue' => ' nov ']);
        tableData($table);

        expect($table->selectAllFiltered())->toBeTrue()
            ->and($table->selected)->toEqualCanonicalizing(['1', '3']);
    });

    it('respects search and header filters together', function () {
        $table = postBulkTable(['searchValue' => 'hotovo', 'headerFilter' => ['score' => '5']]);
        tableData($table);
        $table->selectAllFiltered();

        expect($table->selected)->toEqualCanonicalizing(['5', '6']);
    });

    it('treats % literally when selecting all', function () {
        $table = postBulkTable(['searchValue' => '100%']);
        tableData($table);
        $table->selectAllFiltered();

        expect($table->selected)->toBe(['5']);
    });
});

// ─── Multiselect filter ───────────────────────────────────────────────────────

describe('multiselect header filter', function () {
    it('keeps rows matching any selected value in the array driver', function () {
        expect(array_column(tableData(arrayTable(['headerFilter' => ['score' => ['10', '30']]])), 'id'))->toBe([1, 2, 4]);
    });

    it('ignores an empty selection in the array driver', function () {
        expect(array_column(tableData(arrayTable(['headerFilter' => ['score' => []]])), 'id'))->toBe([1, 2, 3, 4, 5]);
    });

    it('keeps rows matching any selected value in the database driver', function () {
        seedPosts();
        Post::whereKey([2, 7])->update(['published' => false]);

        expect(array_column(tableData(postTable(['headerFilter' => ['published' => ['0']]])), 'id'))->toBe([2, 7])
            ->and(tableData(postTable(['headerFilter' => ['published' => ['0', '1']]])))->toHaveCount(8);
    });

    it('ignores an empty selection in the database driver', function () {
        seedPosts();

        expect(tableData(postTable(['headerFilter' => ['published' => []]])))->toHaveCount(8);
    });
});

describe('database driver LIKE escaping', function () {
    beforeEach(fn () => seedPosts());

    function executedSql(Closure $run): string
    {
        $sql = [];
        Illuminate\Support\Facades\DB::listen(function ($query) use (&$sql) {
            $sql[] = $query->sql;
        });
        $run();

        return implode("\n", $sql);
    }

    it('sends a plain LIKE when the value has nothing to escape', function () {
        expect(executedSql(fn () => tableData(postTable(['searchValue' => 'Svo*da', 'headerFilter' => ['title' => 'Petr']]))))
            ->toContain(' LIKE ?')
            ->not->toContain('ESCAPE');
    });

    it('adds ESCAPE only for values containing % or _', function (string $value) {
        expect(executedSql(fn () => tableData(postTable(['searchValue' => $value]))))->toContain("ESCAPE '!'");
    })->with(['100%', 'a_b', 'wow!']);
});
