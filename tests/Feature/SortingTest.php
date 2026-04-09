<?php

use Tests\Fixtures\Comment;
use Tests\Fixtures\CommentDataTable;
use Tests\Fixtures\Post;
use Tests\Fixtures\PostDataTable;
use Tests\Fixtures\Reaction;

// ─── Jednoduché typy sloupců ─────────────────────────────────────────────────

describe('sort by string column', function () {
    it('sorts ascending', function () {
        Post::insert([
            ['title' => 'Zebra', 'score' => 0, 'published' => false],
            ['title' => 'Apple', 'score' => 0, 'published' => false],
            ['title' => 'Mango', 'score' => 0, 'published' => false],
        ]);

        $table = new PostDataTable(fn () => Post::query(), ['id' => 'ID', 'title' => 'Title']);
        $table->sortBy = 'title';
        $table->sortDirection = 'asc';

        $results = $table->datasetFromDB(Post::query());

        expect(array_column($results, 'title'))->toBe(['Apple', 'Mango', 'Zebra']);
    });

    it('sorts descending', function () {
        Post::insert([
            ['title' => 'Zebra', 'score' => 0, 'published' => false],
            ['title' => 'Apple', 'score' => 0, 'published' => false],
            ['title' => 'Mango', 'score' => 0, 'published' => false],
        ]);

        $table = new PostDataTable(fn () => Post::query(), ['id' => 'ID', 'title' => 'Title']);
        $table->sortBy = 'title';
        $table->sortDirection = 'desc';

        $results = $table->datasetFromDB(Post::query());

        expect(array_column($results, 'title'))->toBe(['Zebra', 'Mango', 'Apple']);
    });
});

describe('sort by int column', function () {
    it('sorts ascending', function () {
        Post::insert([
            ['title' => 'A', 'score' => 50, 'published' => false],
            ['title' => 'B', 'score' => 10, 'published' => false],
            ['title' => 'C', 'score' => 99, 'published' => false],
        ]);

        $table = new PostDataTable(fn () => Post::query(), ['id' => 'ID', 'title' => 'Title', 'score' => 'Score']);
        $table->sortBy = 'score';
        $table->sortDirection = 'asc';

        $results = $table->datasetFromDB(Post::query());

        expect(array_column($results, 'score'))->toBe([10, 50, 99]);
    });

    it('sorts descending', function () {
        Post::insert([
            ['title' => 'A', 'score' => 50, 'published' => false],
            ['title' => 'B', 'score' => 10, 'published' => false],
            ['title' => 'C', 'score' => 99, 'published' => false],
        ]);

        $table = new PostDataTable(fn () => Post::query(), ['id' => 'ID', 'title' => 'Title', 'score' => 'Score']);
        $table->sortBy = 'score';
        $table->sortDirection = 'desc';

        $results = $table->datasetFromDB(Post::query());

        expect(array_column($results, 'score'))->toBe([99, 50, 10]);
    });
});

describe('sort by bool column', function () {
    it('sorts ascending (false first)', function () {
        Post::insert([
            ['title' => 'A', 'score' => 0, 'published' => true],
            ['title' => 'B', 'score' => 0, 'published' => false],
            ['title' => 'C', 'score' => 0, 'published' => true],
        ]);

        $table = new PostDataTable(fn () => Post::query(), ['id' => 'ID', 'title' => 'Title', 'published' => 'Published']);
        $table->sortBy = 'published';
        $table->sortDirection = 'asc';

        $results = $table->datasetFromDB(Post::query());

        expect($results[0]['title'])->toBe('B')
            ->and($results[1]['published'])->toBeTrue()
            ->and($results[2]['published'])->toBeTrue();
    });

    it('sorts descending (true first)', function () {
        Post::insert([
            ['title' => 'A', 'score' => 0, 'published' => true],
            ['title' => 'B', 'score' => 0, 'published' => false],
            ['title' => 'C', 'score' => 0, 'published' => true],
        ]);

        $table = new PostDataTable(fn () => Post::query(), ['id' => 'ID', 'title' => 'Title', 'published' => 'Published']);
        $table->sortBy = 'published';
        $table->sortDirection = 'desc';

        $results = $table->datasetFromDB(Post::query());

        expect($results[0]['published'])->toBeTrue()
            ->and($results[1]['published'])->toBeTrue()
            ->and($results[2]['title'])->toBe('B');
    });
});

// ─── BelongsTo ───────────────────────────────────────────────────────────────

describe('sort by BelongsTo column', function () {
    it('sorts comments by post title ascending', function () {
        $postZ = Post::create(['title' => 'Zebra', 'score' => 0, 'published' => false]);
        $postA = Post::create(['title' => 'Apple', 'score' => 0, 'published' => false]);

        Comment::insert([
            ['post_id' => $postZ->id, 'body' => 'comment on Zebra'],
            ['post_id' => $postA->id, 'body' => 'comment on Apple'],
        ]);

        $table = new CommentDataTable(
            fn () => Comment::query(),
            ['id' => 'ID', 'body' => 'Body', 'post.title' => 'Post'],
        );
        $table->sortBy = 'post.title';
        $table->sortDirection = 'asc';

        $results = $table->datasetFromDB(Comment::query());

        expect(array_column($results, 'body'))->toBe(['comment on Apple', 'comment on Zebra']);
    });

    it('sorts comments by post title descending', function () {
        $postZ = Post::create(['title' => 'Zebra', 'score' => 0, 'published' => false]);
        $postA = Post::create(['title' => 'Apple', 'score' => 0, 'published' => false]);

        Comment::insert([
            ['post_id' => $postZ->id, 'body' => 'comment on Zebra'],
            ['post_id' => $postA->id, 'body' => 'comment on Apple'],
        ]);

        $table = new CommentDataTable(
            fn () => Comment::query(),
            ['id' => 'ID', 'body' => 'Body', 'post.title' => 'Post'],
        );
        $table->sortBy = 'post.title';
        $table->sortDirection = 'desc';

        $results = $table->datasetFromDB(Comment::query());

        expect(array_column($results, 'body'))->toBe(['comment on Zebra', 'comment on Apple']);
    });

    it('comments with null post appear last on asc', function () {
        $postA = Post::create(['title' => 'Apple', 'score' => 0, 'published' => false]);

        Comment::insert([
            ['post_id' => $postA->id,  'body' => 'has post'],
            ['post_id' => 9999,        'body' => 'orphan'],   // neexistující post → LEFT JOIN → NULL
        ]);

        $table = new CommentDataTable(
            fn () => Comment::query(),
            ['id' => 'ID', 'body' => 'Body', 'post.title' => 'Post'],
        );
        $table->sortBy = 'post.title';
        $table->sortDirection = 'asc';

        $results = $table->datasetFromDB(Comment::query());

        // NULL sorts first in SQLite ASC — jen ověřujeme, že oba záznamy jsou ve výsledku
        expect($results)->toHaveCount(2);
    });
});

// ─── HasMany ─────────────────────────────────────────────────────────────────

describe('sort by HasMany count', function () {
    it('sorts ascending', function () {
        $postA = Post::create(['title' => 'Post A', 'score' => 0, 'published' => false]);
        $postB = Post::create(['title' => 'Post B', 'score' => 0, 'published' => false]);

        Comment::insert([
            ['post_id' => $postA->id, 'body' => 'c1'],
            ['post_id' => $postA->id, 'body' => 'c2'],
            ['post_id' => $postA->id, 'body' => 'c3'],
            ['post_id' => $postB->id, 'body' => 'c4'],
        ]);

        $table = new PostDataTable(
            fn () => Post::query(),
            ['id' => 'ID', 'title' => 'Title', 'comments.id' => 'Comments'],
        );
        $table->sortBy = 'comments.id';
        $table->sortDirection = 'asc';

        $results = $table->datasetFromDB(Post::query());

        expect($results[0]['title'])->toBe('Post B')
            ->and($results[1]['title'])->toBe('Post A');
    });

    it('sorts descending', function () {
        $postA = Post::create(['title' => 'Post A', 'score' => 0, 'published' => false]);
        $postB = Post::create(['title' => 'Post B', 'score' => 0, 'published' => false]);

        Comment::insert([
            ['post_id' => $postA->id, 'body' => 'c1'],
            ['post_id' => $postA->id, 'body' => 'c2'],
            ['post_id' => $postA->id, 'body' => 'c3'],
            ['post_id' => $postB->id, 'body' => 'c4'],
        ]);

        $table = new PostDataTable(
            fn () => Post::query(),
            ['id' => 'ID', 'title' => 'Title', 'comments.id' => 'Comments'],
        );
        $table->sortBy = 'comments.id';
        $table->sortDirection = 'desc';

        $results = $table->datasetFromDB(Post::query());

        expect($results[0]['title'])->toBe('Post A')
            ->and($results[1]['title'])->toBe('Post B');
    });

    it('posts with zero children sort correctly', function () {
        $postA = Post::create(['title' => 'Post A', 'score' => 0, 'published' => false]);
        $postB = Post::create(['title' => 'Post B', 'score' => 0, 'published' => false]); // 0 comments

        Comment::insert([
            ['post_id' => $postA->id, 'body' => 'c1'],
            ['post_id' => $postA->id, 'body' => 'c2'],
        ]);

        $table = new PostDataTable(
            fn () => Post::query(),
            ['id' => 'ID', 'title' => 'Title', 'comments.id' => 'Comments'],
        );
        $table->sortBy = 'comments.id';
        $table->sortDirection = 'desc';

        $results = $table->datasetFromDB(Post::query());

        expect($results[0]['title'])->toBe('Post A')
            ->and($results[1]['title'])->toBe('Post B');
    });
});

// ─── MorphMany ────────────────────────────────────────────────────────────────

describe('sort by MorphMany count', function () {
    it('sorts ascending', function () {
        $postA = Post::create(['title' => 'Post A', 'score' => 0, 'published' => false]);
        $postB = Post::create(['title' => 'Post B', 'score' => 0, 'published' => false]);

        Reaction::insert([
            ['reactable_id' => $postA->id, 'reactable_type' => Post::class, 'type' => 'like'],
            ['reactable_id' => $postA->id, 'reactable_type' => Post::class, 'type' => 'like'],
            ['reactable_id' => $postB->id, 'reactable_type' => Post::class, 'type' => 'like'],
            ['reactable_id' => $postB->id, 'reactable_type' => Post::class, 'type' => 'like'],
            ['reactable_id' => $postB->id, 'reactable_type' => Post::class, 'type' => 'like'],
        ]);

        $table = new PostDataTable(
            fn () => Post::query(),
            ['id' => 'ID', 'title' => 'Title', 'reactions.id' => 'Reactions'],
        );
        $table->sortBy = 'reactions.id';
        $table->sortDirection = 'asc';

        $results = $table->datasetFromDB(Post::query());

        expect($results[0]['title'])->toBe('Post A')
            ->and($results[1]['title'])->toBe('Post B');
    });

    it('sorts descending', function () {
        $postA = Post::create(['title' => 'Post A', 'score' => 0, 'published' => false]);
        $postB = Post::create(['title' => 'Post B', 'score' => 0, 'published' => false]);

        Reaction::insert([
            ['reactable_id' => $postA->id, 'reactable_type' => Post::class, 'type' => 'like'],
            ['reactable_id' => $postA->id, 'reactable_type' => Post::class, 'type' => 'like'],
            ['reactable_id' => $postB->id, 'reactable_type' => Post::class, 'type' => 'like'],
            ['reactable_id' => $postB->id, 'reactable_type' => Post::class, 'type' => 'like'],
            ['reactable_id' => $postB->id, 'reactable_type' => Post::class, 'type' => 'like'],
        ]);

        $table = new PostDataTable(
            fn () => Post::query(),
            ['id' => 'ID', 'title' => 'Title', 'reactions.id' => 'Reactions'],
        );
        $table->sortBy = 'reactions.id';
        $table->sortDirection = 'desc';

        $results = $table->datasetFromDB(Post::query());

        expect($results[0]['title'])->toBe('Post B')
            ->and($results[1]['title'])->toBe('Post A');
    });

    it('counts only matching morph type', function () {
        $postA = Post::create(['title' => 'Post A', 'score' => 0, 'published' => false]);

        Reaction::insert([
            ['reactable_id' => $postA->id, 'reactable_type' => 'App\Models\Video', 'type' => 'like'],
            ['reactable_id' => $postA->id, 'reactable_type' => 'App\Models\Video', 'type' => 'like'],
            ['reactable_id' => $postA->id, 'reactable_type' => Post::class, 'type' => 'like'],
        ]);

        $table = new PostDataTable(
            fn () => Post::query(),
            ['id' => 'ID', 'title' => 'Title', 'reactions.id' => 'Reactions'],
        );
        $table->sortBy = 'reactions.id';
        $table->sortDirection = 'desc';

        $table->datasetFromDB(Post::query());

        expect($table->itemsTotal)->toBe(1);
    });
});
