<?php

use Tests\Fixtures\ArrayDataTable;

function bulkActionsRows(): array
{
    return [
        ['id' => 1, 'title' => 'A'],
        ['id' => 2, 'title' => 'B'],
        ['id' => 3, 'title' => 'C'],
        ['id' => 4, 'title' => 'D'],
    ];
}

describe('HasBulkActions trait', function () {
    it('auto-enables selectable when the trait is booted', function () {
        $table = new ArrayDataTable(bulkActionsRows());

        expect($table->selectable)->toBeFalse();

        $table->bootHasBulkActions();

        expect($table->selectable)->toBeTrue();
    });

    it('selects a single row', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->refreshSelectionState($table->currentPageRows());

        $table->selected = [1];
        $table->updatedSelected();

        expect($table->selected)->toBe(['1']);
    });

    it('select-page checkbox only selects rows on the current page', function () {
        $table = new ArrayDataTable(bulkActionsRows());
        $table->refreshSelectionState($table->currentPageRows()); // page 1: ids 1, 2

        $table->updatedSelectPage(true);

        expect($table->selected)->toEqualCanonicalizing(['1', '2']);
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
});
