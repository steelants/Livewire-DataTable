<?php

use Tests\Fixtures\ScrollDataTable;

describe('UseLoadOnScroll', function () {
    it('turns pagination on when booted', function () {
        $table = new ScrollDataTable(itemsTotal: 100, itemsPerPage: 10);

        expect($table->paginated)->toBeTrue();
    });

    it('derives the step from the initial itemsPerPage', function () {
        $table = new ScrollDataTable(itemsTotal: 100, itemsPerPage: 25);

        expect($table->loadMoreStep)->toBe(25);

        $table->loadMore();

        // It used to be hardcoded +100, so the first batch of 25 rows jumped to 125.
        expect($table->itemsPerPage)->toBe(50);
    });

    it('respects an explicit step', function () {
        $table = new ScrollDataTable(itemsTotal: 100, itemsPerPage: 10, loadMoreStep: 40);

        $table->loadMore();

        expect($table->itemsPerPage)->toBe(50);
    });

    it('stops when everything is loaded', function () {
        $table = new ScrollDataTable(itemsTotal: 25, itemsPerPage: 10);

        $table->loadMore(); // 20
        expect($table->canLoadMore)->toBeTrue();

        $table->loadMore(); // 30 >= 25

        expect($table->itemsPerPage)->toBe(30)
            ->and($table->canLoadMore)->toBeFalse();
    });

    it('does nothing once there is nothing left to load', function () {
        $table = new ScrollDataTable(itemsTotal: 5, itemsPerPage: 10);
        $table->refreshLoadMoreState();

        $table->loadMore();

        expect($table->itemsPerPage)->toBe(10);
    });

    it('knows there is nothing to load before the first loadMore call', function () {
        // Keeps the trigger hidden, so x-intersect doesn't fire a request just
        // to find out there's nothing else left.
        $table = new ScrollDataTable(itemsTotal: 4, itemsPerPage: 10);

        $table->refreshLoadMoreState();

        expect($table->canLoadMore)->toBeFalse();
    });

    it('keeps loading while rows remain', function () {
        $table = new ScrollDataTable(itemsTotal: 40, itemsPerPage: 10);

        $table->refreshLoadMoreState();

        expect($table->canLoadMore)->toBeTrue();
    });
});
