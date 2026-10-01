<?php

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

/**
 * PostHiddenSearchComponent redeclares searchableColumns without #[Locked], so the browser
 * can write it - assigning the property here is what a forged Livewire update does.
 */
function unlockedTable(array $props): PostHiddenSearchComponent
{
    $table = new PostHiddenSearchComponent();
    foreach ($props as $name => $value) {
        $table->{$name} = $value;
    }

    return $table;
}

describe('unlocked searchableColumns from the browser', function () {
    it('does not call model methods through a fake relation', function (string $column) {
        $table = unlockedTable(['searchValue' => 'zzz', 'searchableColumns' => [$column]]);

        // Ignored column: the search has nothing left to restrict, the table is untouched.
        expect(fn () => tableData($table))->not->toThrow(Throwable::class)
            ->and(Post::count())->toBe(8);
    })->with(['truncate.x', 'delete.x', 'newQuery.x', 'getConnection.x']);

    it('ignores columns that are neither headers nor declared in code', function () {
        $table = unlockedTable(['searchValue' => '1', 'searchableColumns' => ['title" IS NOT NULL OR "title', 'published']]);

        expect(fn () => tableData($table))->not->toThrow(Throwable::class);
    });

    it('keeps the allowed columns of a tampered list', function () {
        $table = unlockedTable(['searchValue' => 'Svoboda', 'searchableColumns' => ['truncate.x', 'title']]);

        expect(array_column(tableData($table), 'title'))->toBe(['Petr Svoboda'])
            ->and(Post::count())->toBe(8);
    });

    it('still searches a hidden column declared in code', function () {
        $table = new PostHiddenSearchComponent();
        $table->searchValue = '30';

        expect(array_column(tableData($table), 'title'))->toBe(['Jan Novák']);
    });

    it('does not reach a fake relation through select all', function () {
        $table = Livewire::test(Tests\Fixtures\PostBulkTableComponent::class)->set('searchValue', 'zzz');

        expect(fn () => $table->set('searchableColumns', ['truncate.x']))
            ->toThrow(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);

        $table->call('selectAllFiltered');

        expect(Post::count())->toBe(8);
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

describe('locked searchableColumns', function () {
    it('rejects a change from the browser', function () {
        Livewire::test(PostTableComponent::class)->set('searchableColumns', ['truncate.x']);
    })->throws(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);

    it('trusts a hidden column set in mount() while the property is locked', function () {
        $table = Livewire::test(Tests\Fixtures\PostMountSearchComponent::class)->set('searchValue', '30');

        expect(array_column($table->viewData('dataset'), 'title'))->toBe(['Jan Novák']);
    });

    it('keeps a forged value of a redeclared property out of the query', function () {
        // PostHiddenSearchComponent redeclares searchableColumns without #[Locked], so Livewire
        // accepts the value - the allowlist in UseDatabase keeps it out of the query.
        $table = Livewire::test(PostHiddenSearchComponent::class)
            ->set('searchableColumns', ['truncate.x'])
            ->set('searchValue', 'Jan');

        expect($table->viewData('dataset'))->toBe([])
            ->and(Post::count())->toBe(8);
    });
});
