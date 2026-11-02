<?php

use Livewire\Livewire;
use Tests\Fixtures\ArrayActionsComponent;
use Tests\Fixtures\Post;
use Tests\Fixtures\PostActionsComponent;

/**
 * Rendered rows as [wire:key id, id sent by the delete action, title].
 *
 * The row key must be the row id, not its position: with a positional key,
 * deleting a row shifted the rows up and Livewire kept the old elements,
 * so the next action was sent with the id of a different row.
 */
function renderedRows(string $html): array
{
    preg_match_all('/<tr wire:key="row-([^"]+)">(.*?)<\/tr>/s', $html, $rows, PREG_SET_ORDER);

    return array_map(function (array $row) {
        preg_match("/wire:click='delete\\(([^)]*)\\)'/", $row[2], $click);
        preg_match_all('/<td>([^<]*)<\/td>/', $row[2], $cells);

        return [$row[1], $click[1] ?? null, trim($cells[1][1] ?? '')];
    }, $rows);
}

describe('row actions', function () {
    it('keys rows and actions by the row id in the database driver', function () {
        Post::insert([
            ['title' => 'First', 'score' => 0, 'published' => true],
            ['title' => 'Second', 'score' => 0, 'published' => true],
            ['title' => 'Third', 'score' => 0, 'published' => true],
            ['title' => 'Fourth', 'score' => 0, 'published' => true],
        ]);

        $table = Livewire::test(PostActionsComponent::class);

        expect(renderedRows($table->html()))->toBe([
            ['1', '1', 'First'],
            ['2', '2', 'Second'],
            ['3', '3', 'Third'],
        ]);

        $table->call('delete', 2);

        // Fourth moves up onto the page with its own key and its own id.
        expect(renderedRows($table->html()))->toBe([
            ['1', '1', 'First'],
            ['3', '3', 'Third'],
            ['4', '4', 'Fourth'],
        ]);

        $table->call('delete', 3);

        expect(renderedRows($table->html()))->toBe([
            ['1', '1', 'First'],
            ['4', '4', 'Fourth'],
        ])->and(Post::pluck('title')->all())->toBe(['First', 'Fourth']);
    });

    it('keys rows and actions by the row id in the array driver', function () {
        ArrayActionsComponent::$rows = [
            ['id' => 10, 'title' => 'First'],
            ['id' => 20, 'title' => 'Second'],
            ['id' => 30, 'title' => 'Third'],
            ['id' => 40, 'title' => 'Fourth'],
        ];

        $table = Livewire::test(ArrayActionsComponent::class);

        expect(renderedRows($table->html()))->toBe([
            ['10', '10', 'First'],
            ['20', '20', 'Second'],
            ['30', '30', 'Third'],
        ]);

        $table->call('delete', 10);

        expect(renderedRows($table->html()))->toBe([
            ['20', '20', 'Second'],
            ['30', '30', 'Third'],
            ['40', '40', 'Fourth'],
        ]);
    });

    it('keeps keys and action ids matched on the next page after a delete', function () {
        ArrayActionsComponent::$rows = array_map(fn ($i) => ['id' => $i, 'title' => "Row $i"], range(1, 7));

        $table = Livewire::test(ArrayActionsComponent::class)->set('currentPage', 2);

        expect(array_column(renderedRows($table->html()), 0))->toBe(['4', '5', '6']);

        $table->call('delete', 1);

        expect(renderedRows($table->html()))->toBe([
            ['5', '5', 'Row 5'],
            ['6', '6', 'Row 6'],
            ['7', '7', 'Row 7'],
        ]);
    });
});
