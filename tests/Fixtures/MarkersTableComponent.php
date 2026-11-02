<?php

namespace Tests\Fixtures;

use Illuminate\Support\Facades\Blade;
use SteelAnts\DataTable\Livewire\DataTableComponent;

/**
 * 8 columns: 4 plain, 2 render casts, 2 renderColumnX() - one of them renders a Blade
 * component with its own @if. Used by the tbody rendering tests and the markers benchmark.
 */
class MarkersTableComponent extends DataTableComponent
{
    public static array $rows = [];

    public bool $paginated = false;
    public bool $sortable = true;

    public static function rows(int $count): array
    {
        $statuses = ['open', 'done', 'blocked'];
        $rows = [];
        for ($i = 1; $i <= $count; $i++) {
            $rows[] = [
                'id'     => $i,
                'name'   => "Task {$i}",
                'email'  => "user{$i}@example.com",
                'note'   => $i % 7 === 0 ? 'a < b & "c"' : "Note {$i}",
                'status' => $statuses[$i % 3],
                'title'  => $i % 4 === 0 ? '<b>bold</b>' : "Title {$i}",
                'active' => $i % 2 === 0,
                'score'  => $i * 3 % 100,
            ];
        }

        return $rows;
    }

    public function dataset(): array
    {
        return static::$rows;
    }

    public function headers(): array
    {
        return [
            'id'     => 'ID',
            'name'   => 'Name',
            'email'  => 'Email',
            'note'   => 'Note',
            'status' => 'Status',
            'title'  => 'Title',
            'active' => 'Active',
            'score'  => 'Score',
        ];
    }

    public function renderCasts(): array
    {
        return ['active' => BoolCast::class, 'score' => ScoreCast::class];
    }

    public function renderColumnStatus(mixed $value, array $row): string
    {
        return Blade::render('<x-bench-badge :color="$color" :icon="$icon">{{ $value }}</x-bench-badge>', [
            'color' => $value === 'done' ? 'success' : 'secondary',
            'icon'  => $value === 'blocked' ? 'fas fa-lock' : null,
            'value' => $value,
        ]);
    }

    public function renderColumnTitle(mixed $value, array $row): string
    {
        return '<a href="/tasks/' . e($row['id']) . '">' . e($value) . '</a>';
    }
}
