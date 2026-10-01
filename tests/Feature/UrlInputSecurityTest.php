<?php

use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\Fixtures\Post;
use Tests\Fixtures\PostHiddenSearchComponent;
use Tests\Fixtures\PostTableComponent;

/**
 * Every public property can be written by the browser - the query string ones
 * (searchValue, sortBy, sortDirection, currentPage, itemsPerPage) even by a link.
 * None of them may reach SQL as anything but a bound value.
 */
beforeEach(fn () => seedPosts());

describe('searchableColumns from the browser', function () {
    it('rejects a fake relation that would call a model method', function (string $component, string $column) {
        $table = Livewire::test($component)->set('searchValue', 'zzz');

        expect(fn () => $table->set('searchableColumns', [$column]))->toThrow(CannotUpdateLockedPropertyException::class)
            ->and(fn () => $table->set('searchableColumns.0', $column))->toThrow(CannotUpdateLockedPropertyException::class)
            ->and(Post::count())->toBe(8);
    })->with(function () {
        foreach ([PostTableComponent::class, PostHiddenSearchComponent::class] as $component) {
            foreach (['truncate.x', 'delete.x', 'newQuery.x', 'title" IS NOT NULL OR "title'] as $column) {
                yield class_basename($component) . ' ' . $column => [$component, $column];
            }
        }
    });

    it('lets the browser pick header columns, e.g. from a column picker', function () {
        $table = Livewire::test(PostTableComponent::class)
            ->set('searchableColumns', ['title'])
            ->set('searchValue', 'Svoboda');

        expect(array_column($table->viewData('dataset'), 'title'))->toBe(['Petr Svoboda']);
    });

    it('lets the browser keep a hidden column the code declared', function () {
        $table = Livewire::test(PostHiddenSearchComponent::class)
            ->set('searchableColumns', ['score'])
            ->set('searchValue', '30');

        expect(array_column($table->viewData('dataset'), 'title'))->toBe(['Jan Novák']);
    });

    it('protects select all as well', function () {
        $table = Livewire::test(Tests\Fixtures\PostBulkTableComponent::class)->set('searchValue', 'zzz');

        expect(fn () => $table->set('searchableColumns', ['truncate.x']))->toThrow(CannotUpdateLockedPropertyException::class);

        $table->call('selectAllFiltered');

        expect(Post::count())->toBe(8);
    });
});

describe('searchableColumns set in code', function () {
    it('searches a hidden column from the declaration', function () {
        $table = new PostHiddenSearchComponent();
        $table->searchValue = '30';

        expect(array_column(tableData($table), 'title'))->toBe(['Jan Novák']);
    });

    it('searches a hidden column set in mount()', function () {
        $table = Livewire::test(Tests\Fixtures\PostMountSearchComponent::class)->set('searchValue', '30');

        expect(array_column($table->viewData('dataset'), 'title'))->toBe(['Jan Novák']);
    });

    it('searches a hidden column set in mount() of a component that redeclares the property', function () {
        $table = Livewire::test(Tests\Fixtures\PostRedeclaredMountSearchComponent::class)->set('searchValue', '30');

        expect(array_column($table->viewData('dataset'), 'title'))->toBe(['Jan Novák']);
    });
});

describe('other configuration from the browser', function () {
    it('rejects another view', function () {
        expect(fn () => Livewire::test(PostTableComponent::class)->set('viewName', 'datatable-components::tbody'))->toThrow(CannotUpdateLockedPropertyException::class);
    });

    it('accepts the current view', function () {
        Livewire::test(PostTableComponent::class)
            ->set('viewName', 'datatable::data-table')
            ->assertSet('viewName', 'datatable::data-table');
    });

    it('rejects a fake sortable column', function () {
        expect(fn () => Livewire::test(PostTableComponent::class)->set('sortableColumns', ['(SELECT 1)']))->toThrow(CannotUpdateLockedPropertyException::class);
    });

    it('keeps a default sort by a hidden column declared in code', function () {
        $table = new Tests\Fixtures\PostHiddenSortComponent();

        expect(array_column(tableData($table), 'id'))->toBe([7, 8, 5, 6, 2, 4, 3, 1]);
    });
});

describe('query string values', function () {
    it('binds a malicious search value as data', function () {
        $table = Livewire::withQueryParams(['searchValue' => "x%' OR 1=1 --"])->test(PostTableComponent::class);

        expect($table->viewData('dataset'))->toBe([])
            ->and(Post::count())->toBe(8);
    });

    it('ignores a raw SQL sortBy from the URL', function () {
        $table = Livewire::withQueryParams([
            'sortBy'        => '(SELECT CASE WHEN 1=1 THEN title ELSE id END)',
            'sortDirection' => 'desc; DELETE FROM posts',
        ])->test(PostTableComponent::class);

        expect(array_column($table->viewData('dataset'), 'id'))->toBe([1, 2, 3, 4, 5, 6, 7, 8])
            ->and(Post::count())->toBe(8);
    });

    it('does not load the whole table for a negative page size', function () {
        $table = Livewire::withQueryParams(['itemsPerPage' => '-1'])->test(Tests\Fixtures\QueryStringComponent::class);

        expect($table->viewData('dataset'))->toHaveCount(2)
            ->and($table->get('itemsPerPage'))->toBe(2);
    });

    it('does not load the whole table for a negative page size in the database driver', function () {
        $table = postTable(['paginated' => true, 'itemsPerPage' => -1]);

        expect(tableData($table))->toHaveCount(8)
            ->and($table->itemsPerPage)->toBe(100);
    });

    it('treats a page below 1 as the first page', function () {
        $table = postTable(['paginated' => true, 'itemsPerPage' => 3, 'currentPage' => -4]);

        expect(array_column(tableData($table), 'id'))->toBe([1, 2, 3])
            ->and($table->currentPage)->toBe(1);
    });
});

