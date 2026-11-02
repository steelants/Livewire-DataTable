<?php

use Tests\Fixtures\Post;
use Tests\Fixtures\PostTableComponent;

/**
 * sortBy and sortDirection are public Livewire properties - the browser can send any value.
 * A sortBy starting with "(" used to go straight into orderByRaw(), which allowed a blind
 * SQL injection: the row order revealed whether a sub-select matched.
 */
function sortedTitles(array $props): array
{
    $table = new PostTableComponent();
    $table->filterable = false;
    foreach ($props as $name => $value) {
        $table->{$name} = $value;
    }

    return array_column(tableData($table), 'title');
}

beforeEach(fn () => Post::insert([
    ['title' => 'b', 'score' => 1, 'published' => true],
    ['title' => 'a', 'score' => 2, 'published' => false],
]));

describe('sorting input from the browser', function () {
    it('ignores a raw SQL expression in sortBy', function (string $probe) {
        $sql = "(CASE WHEN (SELECT COUNT(*) FROM posts WHERE title = '{$probe}') > 0 THEN title ELSE id END)";

        // Same order whatever the sub-select finds: the expression is not executed.
        expect(sortedTitles(['sortBy' => $sql]))->toBe(['b', 'a']);
    })->with(['a', 'zzz']);

    it('ignores a column that is not a header', function () {
        expect(sortedTitles(['sortBy' => 'published']))->toBe(['b', 'a']);
    });

    it('still sorts by a header column', function () {
        expect(sortedTitles(['sortBy' => 'title']))->toBe(['a', 'b'])
            ->and(sortedTitles(['sortBy' => 'title', 'sortDirection' => 'desc']))->toBe(['b', 'a']);
    });

    it('falls back to ascending for an invalid direction', function () {
        expect(sortedTitles(['sortBy' => 'title', 'sortDirection' => 'desc, (SELECT 1)']))->toBe(['a', 'b']);
    });
});
