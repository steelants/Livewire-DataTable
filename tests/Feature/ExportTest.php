<?php

use Livewire\Livewire;
use Tests\Fixtures\ArrayExportComponent;
use Tests\Fixtures\Post;
use Tests\Fixtures\PostExportComponent;

/**
 * The CSV a Livewire call downloads: [file name, list of lines split into cells].
 */
function downloadedCsv($testable): array
{
    $download = $testable->effects['download'] ?? null;
    expect($download)->not->toBeNull();

    $content = base64_decode($download['content']);
    expect(substr($content, 0, 3))->toBe("\xEF\xBB\xBF");

    $lines = array_map(
        fn ($line) => str_getcsv($line, ';', '"', ''),
        array_values(array_filter(explode("\n", substr($content, 3)), fn ($line) => $line !== ''))
    );

    return [$download['name'], $lines];
}

beforeEach(function () {
    ArrayExportComponent::$rows = [
        ['id' => 1, 'name' => 'Jan', 'city' => 'Praha', 'note' => 'a;"b"'],
        ['id' => 2, 'name' => 'Eva', 'city' => 'Brno', 'note' => '=SUM(A1:A9)'],
        ['id' => 3, 'name' => 'Petr', 'city' => 'Praha', 'note' => -5],
        ['id' => 4, 'name' => 'Jana', 'city' => 'Brno', 'note' => ['x' => 1]],
        ['id' => 5, 'name' => 'Karel', 'city' => 'Praha', 'note' => true],
    ];
});

describe('array driver export', function () {
    it('exports every row, not only the visible page, with header labels', function () {
        [$name, $lines] = downloadedCsv(Livewire::test(ArrayExportComponent::class)->call('exportCsv'));

        expect($name)->toBe('people.csv')
            ->and($lines[0])->toBe(['ID', 'Name', 'City', 'Note'])
            ->and(array_column(array_slice($lines, 1), 0))->toBe(['1', '2', '3', '4', '5']);
    });

    it('applies row() and column methods', function () {
        [, $lines] = downloadedCsv(Livewire::test(ArrayExportComponent::class)->call('exportCsv'));

        expect(array_column(array_slice($lines, 1), 1))->toBe(['JAN', 'EVA', 'PETR', 'JANA', 'KAREL']);
    });

    it('respects search, filters and sorting', function () {
        $table = Livewire::test(ArrayExportComponent::class)
            ->set('headerFilter', ['city' => 'Praha'])
            ->set('searchValue', ' a ')
            ->set('sortBy', 'name')
            ->set('sortDirection', 'desc');

        [, $lines] = downloadedCsv($table->call('exportCsv'));

        // Praha + contains "a": Jan, Karel (Petr has no "a"); sorted by name desc.
        expect(array_column(array_slice($lines, 1), 1))->toBe(['KAREL', 'JAN']);
    });

    it('exports only the header line for an empty result', function () {
        [, $lines] = downloadedCsv(Livewire::test(ArrayExportComponent::class)->set('searchValue', 'nobody')->call('exportCsv'));

        expect($lines)->toBe([['ID', 'Name', 'City', 'Note']]);
    });

    it('escapes values and neutralises formulas', function () {
        [, $lines] = downloadedCsv(Livewire::test(ArrayExportComponent::class)->call('exportCsv'));

        expect(array_column(array_slice($lines, 1), 3))->toBe([
            'a;"b"',
            "'=SUM(A1:A9)",
            '-5',
            '{"x":1}',
            '1',
        ]);
    });

    it('keeps serv() as an alias', function () {
        [$name, $lines] = downloadedCsv(Livewire::test(ArrayExportComponent::class)->call('serv'));

        expect($name)->toBe('people.csv')->and($lines)->toHaveCount(6);
    });
});

describe('database driver export', function () {
    beforeEach(fn () => seedPosts());

    it('exports every row across pages with a custom file name', function () {
        [$name, $lines] = downloadedCsv(Livewire::test(PostExportComponent::class)->call('exportCsv'));

        expect($name)->toBe('posts-2.csv')
            ->and($lines[0])->toBe(['ID', 'Title', 'Score', 'Published'])
            ->and(array_column(array_slice($lines, 1), 0))->toBe(['1', '2', '3', '4', '5', '6', '7', '8'])
            ->and($lines[1])->toBe(['1', 'Jan Novák', '30', '1']);
    });

    it('respects search, filters and sorting', function () {
        $table = Livewire::test(PostExportComponent::class)
            ->set('searchValue', 'hotovo')
            ->set('sortBy', 'title')
            ->set('sortDirection', 'desc');

        [, $lines] = downloadedCsv($table->call('exportCsv'));

        expect(array_column(array_slice($lines, 1), 1))->toBe(['100% hotovo', '100 hotovo']);

        [, $lines] = downloadedCsv(Livewire::test(PostExportComponent::class)->set('headerFilter', ['score' => '10'])->call('exportCsv'));

        expect(array_column(array_slice($lines, 1), 1))->toBe(['Petr Svoboda', 'Karel Dvořák']);
    });

    it('exports only the header line for an empty result', function () {
        [, $lines] = downloadedCsv(Livewire::test(PostExportComponent::class)->set('searchValue', 'nobody')->call('exportCsv'));

        expect($lines)->toBe([['ID', 'Title', 'Score', 'Published']]);
    });

    it('reads large tables in chunks and exports every row', function () {
        Post::query()->delete();
        $rows = [];
        for ($i = 1; $i <= 2500; $i++) {
            $rows[] = ['title' => "Post $i", 'score' => $i, 'published' => true];
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            Post::insert($chunk);
        }

        [, $lines] = downloadedCsv(Livewire::test(PostExportComponent::class)->call('exportCsv'));

        expect($lines)->toHaveCount(2501)
            ->and(array_unique(array_column(array_slice($lines, 1), 0)))->toHaveCount(2500);
    });

    it('applies the search before the first render', function () {
        $table = new PostExportComponent();
        $table->searchValue = 'Svoboda';

        ob_start();
        $table->exportCsv()->sendContent();
        $content = ob_get_clean();

        expect(substr_count($content, "\n"))->toBe(2)
            ->and($content)->toContain('Petr Svoboda');
    });
});
