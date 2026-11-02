<?php

namespace Tests\Fixtures;

use Illuminate\View\Component;

/**
 * Stand-in for an application badge component (like x-badge): its own @if adds markers
 * inside the cell, which the table cannot remove.
 */
class BenchBadge extends Component
{
    public function __construct(
        public string $color = 'secondary',
        public ?string $icon = null,
    ) {
    }

    public function render(): string
    {
        return <<<'blade'
            <span class="badge bg-{{ $color }}">@if ($icon)<i class="{{ $icon }}"></i> @endif{{ $slot }}</span>
            blade;
    }
}
