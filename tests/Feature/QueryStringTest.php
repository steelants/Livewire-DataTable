<?php

use Livewire\Livewire;
use Tests\Fixtures\QueryStringComponent;
use Tests\Fixtures\ScrollQueryStringComponent;

beforeEach(function () {
    QueryStringComponent::$rows = array_map(
        fn ($i) => ['id' => $i, 'name' => $i % 2 ? "odd $i" : "even $i", 'score' => $i],
        range(1, 9)
    );
});

describe('query string', function () {
    it('lists the bound properties for a full page load', function () {
        $table = new QueryStringComponent();
        $table->useUrl = true;

        expect($table->queryString())->toBe([
            'currentPage' => ['except' => 0],
            'searchValue',
            'itemsPerPage',
            'sortBy',
            'sortDirection' => ['except' => 'asc'],
        ]);
    });

    it('leaves out disabled features', function () {
        $table = new QueryStringComponent();
        $table->useUrl = true;
        $table->paginated = false;
        $table->searchable = false;
        $table->sortable = false;

        expect($table->queryString())->toBe(['itemsPerPage']);
    });

    it('leaves out itemsPerPage with load on scroll', function () {
        $table = new ScrollQueryStringComponent();
        $table->useUrl = true;

        expect($table->queryString())->not->toContain('itemsPerPage');
    });

    it('binds nothing when the URL is disabled', function () {
        $table = new QueryStringComponent();
        $table->useUrl = false;

        expect($table->queryString())->toBe([]);
    });

    it('enables the URL on a full page load and disables it for Livewire requests', function () {
        $table = new QueryStringComponent();
        $table->mount();
        expect($table->useUrl)->toBeTrue();

        request()->headers->set('X-Livewire', 'true');
        $table = new QueryStringComponent();
        $table->mount();
        expect($table->useUrl)->toBeFalse();
    });

    it('keeps an explicitly configured useUrl', function () {
        $table = new QueryStringComponent();
        $table->useUrl = false;
        $table->mount();

        expect($table->useUrl)->toBeFalse();
    });

    it('restores the table state from the URL', function () {
        $table = Livewire::withQueryParams([
            'searchValue'   => 'odd',
            'sortBy'        => 'score',
            'sortDirection' => 'desc',
            'currentPage'   => '2',
            'itemsPerPage'  => '2',
        ])->test(QueryStringComponent::class);

        $table->assertSet('searchValue', 'odd')
            ->assertSet('sortBy', 'score')
            ->assertSet('sortDirection', 'desc')
            ->assertSet('currentPage', 2)
            ->assertSet('itemsPerPage', 2);

        // odd rows sorted desc: 9, 7 | 5, 3 | 1
        expect(array_column($table->viewData('dataset'), 'id'))->toBe([5, 3]);
    });

    it('resets to the first page when the search changes', function () {
        Livewire::withQueryParams(['currentPage' => '3'])
            ->test(QueryStringComponent::class)
            ->assertSet('currentPage', 3)
            ->set('searchValue', 'even')
            ->assertSet('currentPage', 1);
    });

    it('resets to the first page when itemsPerPage changes', function () {
        Livewire::withQueryParams(['currentPage' => '3'])
            ->test(QueryStringComponent::class)
            ->set('itemsPerPage', 5)
            ->assertSet('currentPage', 1);
    });
});
