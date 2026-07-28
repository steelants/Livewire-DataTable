<?php

use Illuminate\Support\Facades\Blade;
use SteelAnts\DataTable\Support\BulkResult;
use Tests\Fixtures\ArrayDataTable;
use Tests\Fixtures\Post;
use Tests\Fixtures\PostBulkDataTable;

function bulkActionsRows(): array
{
    return [
        ['id' => 1, 'title' => 'A'],
        ['id' => 2, 'title' => 'B'],
        ['id' => 3, 'title' => 'C'],
        ['id' => 4, 'title' => 'D'],
    ];
}

describe('HasBulkActions selection state', function () {
    it('enables selection when the trait boots', function () {
        $table = new ArrayDataTable(bulkActionsRows());

        expect($table->selectable)->toBeTrue()
            ->and($table->selectionEnabled())->toBeTrue();
    });

    it('keeps selection keys as strings', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->refreshSelectionState($table->currentPageRows());

        $table->selected = [1];
        $table->updatedSelected();

        // Numericke stringy pouzite jako klice pole se v PHP meni na inty -
        // deduplikace proto musi hodnoty na konci vzdy pretypovat zpatky.
        expect($table->selected)->toBe(['1']);
    });

    it('deduplicates the selection', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->refreshSelectionState($table->currentPageRows());

        $table->selected = ['1', 1, '1', '2'];
        $table->updatedSelected();

        expect($table->selected)->toBe(['1', '2']);
    });

    it('drops values that cannot be a row key', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->refreshSelectionState($table->currentPageRows());

        $table->selected = ['1', '', null, ['nested'], true];
        $table->updatedSelected();

        expect($table->selected)->toBe(['1']);
    });

    it('select-page checkbox only selects rows on the current page', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->refreshSelectionState($table->currentPageRows()); // page 1: ids 1, 2

        $table->updatedSelectPage(true);

        expect($table->selected)->toEqualCanonicalizing(['1', '2']);
    });

    it('select-page checkbox unselects only rows on the current page', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->selected = ['1', '2', '3'];
        $table->refreshSelectionState($table->currentPageRows()); // page 1: ids 1, 2

        $table->updatedSelectPage(false);

        expect($table->selected)->toEqualCanonicalizing(['3']);
    });

    it('persists selection across pages', function () {
        $table = new ArrayDataTable(bulkActionsRows());

        // Page 1: select all visible rows.
        $table->refreshSelectionState($table->currentPageRows()); // ids 1, 2
        $table->updatedSelectPage(true);

        expect($table->selected)->toEqualCanonicalizing(['1', '2']);

        // Move to page 2.
        $table->currentPage = 2;
        $table->refreshSelectionState($table->currentPageRows()); // ids 3, 4

        // Page 1 selection must survive the page change.
        expect($table->selected)->toEqualCanonicalizing(['1', '2'])
            ->and($table->selectPage)->toBeFalse();

        // Select page 2 as well.
        $table->updatedSelectPage(true);

        expect($table->selected)->toEqualCanonicalizing(['1', '2', '3', '4']);

        // Going back to page 1, the header checkbox reflects that page's state.
        $table->currentPage = 1;
        $table->refreshSelectionState($table->currentPageRows());

        expect($table->selectPage)->toBeTrue()
            ->and($table->selected)->toEqualCanonicalizing(['1', '2', '3', '4']);
    });

    it('clears the whole selection', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->refreshSelectionState($table->currentPageRows());
        $table->updatedSelectPage(true);

        $table->clearSelection();

        expect($table->selected)->toBe([])
            ->and($table->selectPage)->toBeFalse();
    });

    it('reports whether a row is selected', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->selected = ['1'];

        expect($table->isSelected(1))->toBeTrue()
            ->and($table->isSelected('1'))->toBeTrue()
            ->and($table->isSelected(2))->toBeFalse()
            ->and($table->isSelected(null))->toBeFalse();
    });
});

describe('HasBulkActions canSelect gate', function () {
    it('refuses to select when canSelect() is false', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->allowSelection = false;

        expect($table->selectionEnabled())->toBeFalse();

        $table->refreshSelectionState($table->currentPageRows());
        $table->updatedSelectPage(true);

        expect($table->selected)->toBe([])
            ->and($table->selectPage)->toBeFalse()
            ->and($table->visibleSelectionKeys)->toBe([]);
    });

    it('drops an existing selection when the gate closes', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->refreshSelectionState($table->currentPageRows());
        $table->updatedSelectPage(true);

        expect($table->selected)->not->toBeEmpty();

        $table->allowSelection = false;
        $table->refreshSelectionState($table->currentPageRows());

        expect($table->selected)->toBe([]);
    });

    it('does not run bulk actions when the gate is closed', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->selected = ['1'];
        $table->allowSelection = false;

        $table->callBulkAction('markSelected');

        expect($table->bulkActionLog)->toBe([]);
    });
});

describe('HasBulkActions clearing on filter change', function () {
    it('clears the selection when a watched property changes', function (string $property) {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->refreshSelectionState($table->currentPageRows());
        $table->updatedSelectPage(true);

        $table->updatedHasBulkActions($property, 'whatever');

        expect($table->selected)->toBe([]);
    })->with([
        'headerFilter',
        'headerFilter.title',
        'searchValue',
        'sortBy',
        'sortDirection',
    ]);

    it('keeps the selection for unrelated properties', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->refreshSelectionState($table->currentPageRows());
        $table->updatedSelectPage(true);

        $table->updatedHasBulkActions('currentPage', 2);

        expect($table->selected)->toEqualCanonicalizing(['1', '2']);
    });

    it('can be switched off', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->clearSelectionOnFilter = false;
        $table->refreshSelectionState($table->currentPageRows());
        $table->updatedSelectPage(true);

        $table->updatedHasBulkActions('searchValue', 'abc');

        expect($table->selected)->toEqualCanonicalizing(['1', '2']);
    });
});

describe('HasBulkActions dispatching', function () {
    it('dispatches an action by name', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->selected = ['1', '2'];

        $table->callBulkAction('markSelected');

        expect($table->bulkActionLog)->toBe([[['1', '2'], 'markSelected']]);
    });

    it('still dispatches by index for backwards compatibility', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->selected = ['1'];

        $table->callBulkAction(0);

        expect($table->bulkActionLog)->toBe([[['1'], 'markSelected']]);
    });

    it('passes extra parameters after the selection', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->selected = ['1'];

        $table->callBulkAction('withParameters');

        expect($table->bulkActionLog)->toBe([[['1'], 'hello']]);
    });

    it('ignores actions that are not declared', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->selected = ['1'];

        $table->callBulkAction('somethingElse');
        $table->callBulkAction(99);

        expect($table->bulkActionLog)->toBe([]);
    });

    it('ignores url actions and missing methods', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->selected = ['1'];

        $table->callBulkAction('Export');
        $table->callBulkAction('missingMethod');

        expect($table->bulkActionLog)->toBe([]);
    });
});

describe('HasBulkActions with Eloquent rows', function () {
    beforeEach(function () {
        Post::create(['title' => 'First', 'score' => 10, 'published' => true]);
        Post::create(['title' => 'Second', 'score' => 20, 'published' => true]);
        Post::create(['title' => 'Third', 'score' => 30, 'published' => true]);
        Post::create(['title' => 'Hidden', 'score' => 40, 'published' => false]);
    });

    it('resolves selection keys from models', function () {
        $table = new PostBulkDataTable();
        $rows = $table->loadRows();

        expect($rows[0])->toBeInstanceOf(Post::class)
            ->and($table->visibleSelectionKeys)->toBe(['1', '2']);
    });

    it('selects all filtered rows across pages', function () {
        $table = new PostBulkDataTable();
        $table->loadRows();

        expect($table->selectAllFiltered())->toBeTrue();

        // Ctvrty post ma published = false, takze do query() nepatri.
        expect($table->selected)->toEqualCanonicalizing(['1', '2', '3']);
    });

    it('respects header filters when selecting all', function () {
        $table = new PostBulkDataTable();
        $table->headerFilter = ['title' => 'Sec'];
        $table->loadRows();

        $table->selectAllFiltered();

        expect($table->selected)->toBe(['2']);
    });

    it('refuses select all across pages when disabled', function () {
        $table = new PostBulkDataTable();
        $table->selectAllAcrossPages = false;
        $table->loadRows();

        expect($table->selectAllFiltered())->toBeFalse()
            ->and($table->selected)->toBe([]);
    });

    it('refuses select all over the configured limit', function () {
        $table = new PostBulkDataTable();
        $table->selectAllLimit = 2;
        $table->loadRows();

        expect($table->selectAllFiltered())->toBeFalse()
            ->and($table->selected)->toBe([]);
    });

    it('never returns rows outside of query() scope', function () {
        $table = new PostBulkDataTable();

        // Podvrzena ID: 4 je mimo scope query(), 999 vubec neexistuje.
        $table->selected = ['1', '4', '999'];

        expect($table->selectedQuery()->pluck('id')->all())->toBe([1]);
    });
});

describe('HasBulkActions eachSelected', function () {
    beforeEach(function () {
        foreach (range(1, 7) as $i) {
            Post::create(['title' => 'Post ' . $i, 'score' => $i, 'published' => true]);
        }
    });

    it('processes every selected row in chunks', function () {
        $table = new PostBulkDataTable();
        $table->bulkChunkSize = 2;
        $table->selected = ['1', '2', '3', '4', '5', '6', '7'];

        $seen = [];
        $result = $table->eachSelected(function (Post $post) use (&$seen) {
            $seen[] = $post->id;

            return true;
        });

        expect($seen)->toBe([1, 2, 3, 4, 5, 6, 7])
            ->and($result)->toBeInstanceOf(BulkResult::class)
            ->and($result->ok)->toBe(7)
            ->and($result->skipped)->toBe(0)
            ->and($result->failed)->toBe(0);
    });

    it('counts skipped rows and their reasons', function () {
        $table = new PostBulkDataTable();
        $table->selected = ['1', '2', '3', '4'];

        $result = $table->eachSelected(function (Post $post) {
            if ($post->id === 1) {
                return true;
            }

            if ($post->id === 2) {
                return false;
            }

            return 'already done';
        });

        expect($result->ok)->toBe(1)
            ->and($result->skipped)->toBe(3)
            ->and($result->reasons)->toBe(['already done' => 2])
            ->and($result->total())->toBe(4)
            ->and($result->hasProblems())->toBeTrue();
    });

    it('isolates a failing row and keeps going', function () {
        $table = new PostBulkDataTable();
        $table->selected = ['1', '2', '3'];

        $seen = [];
        $result = $table->eachSelected(function (Post $post) use (&$seen) {
            if ($post->id === 2) {
                throw new RuntimeException('boom');
            }

            $seen[] = $post->id;

            return true;
        });

        expect($seen)->toBe([1, 3])
            ->and($result->ok)->toBe(2)
            ->and($result->failed)->toBe(1);
    });

    it('treats a callback returning nothing as processed', function () {
        $table = new PostBulkDataTable();
        $table->selected = ['1'];

        $result = $table->eachSelected(function (Post $post) {
            // zadny return
        });

        expect($result->ok)->toBe(1);
    });

    it('does nothing with an empty selection', function () {
        $table = new PostBulkDataTable();

        $called = false;
        $result = $table->eachSelected(function () use (&$called) {
            $called = true;

            return true;
        });

        expect($called)->toBeFalse()
            ->and($result->isEmpty())->toBeTrue();
    });

    it('skips rows outside of query() scope', function () {
        $hidden = Post::create(['title' => 'Hidden', 'score' => 1, 'published' => false]);

        $table = new PostBulkDataTable();
        $table->selected = ['1', (string) $hidden->id];

        $seen = [];
        $table->eachSelected(function (Post $post) use (&$seen) {
            $seen[] = $post->id;

            return true;
        });

        expect($seen)->toBe([1]);
    });
});

describe('HasBulkActions result message', function () {
    it('summarises a clean run', function () {
        $table = new ArrayDataTable(bulkActionsRows());

        expect($table->bulkResultMessage(new BulkResult(ok: 3)))->toContain('3');
    });

    it('mentions skipped rows and reasons', function () {
        $table = new ArrayDataTable(bulkActionsRows());

        $message = $table->bulkResultMessage(
            new BulkResult(ok: 8, skipped: 4, reasons: ['already checked in' => 4])
        );

        expect($message)->toContain('8')
            ->and($message)->toContain('12')
            ->and($message)->toContain('already checked in');
    });

    it('mentions failures', function () {
        $table = new ArrayDataTable(bulkActionsRows());

        expect($table->bulkResultMessage(new BulkResult(ok: 1, failed: 2)))->toContain('2');
    });

    it('handles an empty result', function () {
        $table = new ArrayDataTable(bulkActionsRows());

        expect($table->bulkResultMessage(new BulkResult()))->not->toBeEmpty();
    });
});

describe('selection cell rendering', function () {
    it('renders the checked attribute for selected rows', function () {
        $html = Blade::render(
            '<x-datatable-selection-cell :selectable="true" :row="$row" key-propery="id" :selected="$selected" />',
            ['row' => ['id' => 7, 'title' => 'A'], 'selected' => ['7']]
        );

        // Bez server-side checked by se stav vyberu po Livewire re-renderu
        // (napr. pri prechodu mezi strankami) neobnovil.
        expect($html)->toContain('checked')
            ->and($html)->toContain('value="7"');
    });

    it('does not render checked for unselected rows', function () {
        $html = Blade::render(
            '<x-datatable-selection-cell :selectable="true" :row="$row" key-propery="id" :selected="$selected" />',
            ['row' => ['id' => 7, 'title' => 'A'], 'selected' => ['8']]
        );

        expect($html)->not->toContain('checked');
    });

    it('renders the checked attribute for model rows', function () {
        $post = Post::create(['title' => 'First', 'score' => 1, 'published' => true]);

        $html = Blade::render(
            '<x-datatable-selection-cell :selectable="true" :row="$row" key-propery="id" :selected="$selected" />',
            ['row' => $post, 'selected' => [(string) $post->id]]
        );

        expect($html)->toContain('checked')
            ->and($html)->toContain('value="' . $post->id . '"');
    });

    it('renders nothing when selection is off', function () {
        $html = Blade::render(
            '<x-datatable-selection-cell :selectable="false" :row="$row" key-propery="id" />',
            ['row' => ['id' => 7]]
        );

        expect(trim($html))->toBe('');
    });
});
