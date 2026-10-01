<?php

use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Tests\Fixtures\BenchBadge;
use Tests\Fixtures\MarkersActionsComponent;
use Tests\Fixtures\MarkersRowComponent;
use Tests\Fixtures\MarkersSelectableComponent;
use Tests\Fixtures\MarkersTableComponent;

/**
 * Rendered HTML of each table variant, 20 rows.
 */
function markersVariant(string $variant): string
{
    MarkersTableComponent::$rows = MarkersTableComponent::rows(20);

    $class = [
        'plain'      => MarkersTableComponent::class,
        'actions'    => MarkersActionsComponent::class,
        'selectable' => MarkersSelectableComponent::class,
        'row'        => MarkersRowComponent::class,
    ][$variant];

    $table = Livewire::test($class);
    if ($variant === 'selectable') {
        $table->set('selected', ['2', '4']);
    }

    return $table->html();
}

function tbodyRows(string $html): array
{
    preg_match_all('/<tr wire:key="(row-[^"]+)">(.*?)<\/tr>/s', normalizedTbody($html), $rows, PREG_SET_ORDER);

    return $rows;
}

beforeEach(fn () => Blade::component('bench-badge', BenchBadge::class));

describe('tbody output', function () {
    // The snapshots were rendered by the template before the morph marker optimisation,
    // so they pin the cell content across template changes. UPDATE_SNAPSHOTS=1 rewrites them.
    it('matches the snapshot', function (string $variant) {
        $file = __DIR__ . "/../Fixtures/snapshots/tbody-{$variant}.html";
        $tbody = normalizedTbody(markersVariant($variant));

        if (getenv('UPDATE_SNAPSHOTS')) {
            @mkdir(dirname($file), 0777, true);
            file_put_contents($file, $tbody . "\n");
        }

        expect($tbody . "\n")->toBe(file_get_contents($file));
    })->with(['plain', 'actions', 'selectable', 'row']);

    it('escapes plain columns and keeps casts and render methods unescaped', function () {
        $row = tbodyRows(markersVariant('plain'))[6][2];

        expect($row)->toContain('<td>a &lt; b &amp; &quot;c&quot;</td>')
            ->and($row)->toContain('<td><span class="score" data-row="7">21 b.</span></td>')
            ->and($row)->toContain('<td><span class="cast-active-no">#7</span></td>')
            ->and(tbodyRows(markersVariant('plain'))[3][2])->toContain('<td><a href="/tasks/4">&lt;b&gt;bold&lt;/b&gt;</a></td>');
    });

    it('renders a Blade component returned by renderColumnX()', function () {
        $rows = tbodyRows(markersVariant('plain'));

        expect($rows[1][2])->toContain('<td><span class="badge bg-secondary"><i class="fas fa-lock"></i> blocked</span></td>')
            ->and($rows[2][2])->toContain('<td><span class="badge bg-secondary">open</span></td>');
    });

    it('lets renderRow() replace casts and render methods', function () {
        $row = tbodyRows(markersVariant('row'))[0][2];

        expect($row)->toStartWith('<td><strong>1</strong></td><td>Task 1</td><td>user1@example.com</td><td>Note 1</td><td>DONE</td><td>Title 1</td><td>no</td><td>3</td>');
    });

    it('keeps the row keys and the cell order', function (string $variant, int $cells) {
        $rows = tbodyRows(markersVariant($variant));

        expect(array_column($rows, 1))->toBe(array_map(fn ($i) => "row-{$i}", range(1, 20)));
        foreach ($rows as $row) {
            expect(substr_count($row[2], '<td'))->toBe($cells);
        }
    })->with([
        ['plain', 8],
        ['actions', 9],
        ['selectable', 10],
        ['row', 9],
    ]);

    it('renders the selection cell only for selectable tables', function () {
        $selectable = tbodyRows(markersVariant('selectable'));

        expect($selectable[1][2])->toStartWith('<td class="datatable-selection-cell"><input type="checkbox" class="form-check-input" wire:model.live="selected" value="2" checked></td>')
            ->and($selectable[2][2])->toStartWith('<td class="datatable-selection-cell"><input type="checkbox" class="form-check-input" wire:model.live="selected" value="3"></td>')
            ->and(normalizedTbody(markersVariant('actions')))->not->toContain('datatable-selection-cell');
    });

    it('renders the actions column, empty for rows without actions', function () {
        $rows = tbodyRows(markersVariant('actions'));

        expect($rows[0][2])->toContain('<td class="text-end"><div class="dropdown position-static">')
            ->and($rows[0][2])->toContain('<a class="dropdown-item " href="/tasks/1/edit"')
            ->and($rows[0][2])->toContain("wire:click='remove(1)'")
            ->and($rows[0][2])->toContain('wire:confirm="Really &quot;delete&quot;?"')
            ->and($rows[0][2])->toContain("onclick='copyRow({&quot;id&quot;:1})'")
            ->and($rows[4][2])->toEndWith('<td class="text-end"></td>')
            ->and(normalizedTbody(markersVariant('plain')))->not->toContain('text-end');
    });
});

describe('morph markers', function () {
    function tbodyMarkers(string $html): int
    {
        preg_match('/<tbody>.*<\/tbody>/s', $html, $tbody);

        return morphMarkerCount($tbody[0]);
    }

    it('adds no markers per row for cells, casts and render methods', function () {
        Tests\Fixtures\RenderComponent::$rows = array_map(
            fn ($i) => ['id' => $i, 'name' => "N{$i}", 'active' => $i % 2 === 0, 'note' => 'x', 'raw' => '<b>y</b>'],
            range(1, 50)
        );

        // Only the @if choosing the loop and the @foreach around the rows; nothing per row or cell.
        expect(tbodyMarkers(Livewire::test(Tests\Fixtures\RenderComponent::class)->html()))->toBe(4)
            ->and(tbodyMarkers(Livewire::test(Tests\Fixtures\RenderRowComponent::class)->html()))->toBe(4);
    });

    it('keeps the markers per row within the budget', function (string $variant, int $perRow) {
        // 20 rows; the bench badge in the status column adds 4 markers per row itself.
        expect(tbodyMarkers(markersVariant($variant)))->toBeLessThanOrEqual(20 * $perRow + 4);
    })->with([
        ['plain', 4],
        ['row', 10],
        ['actions', 14],
        ['selectable', 18],
    ]);
});
