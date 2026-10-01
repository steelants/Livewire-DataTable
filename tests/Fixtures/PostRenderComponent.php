<?php

namespace Tests\Fixtures;

/**
 * Database driver with a render cast and a renderColumnX() method.
 */
class PostRenderComponent extends PostTableComponent
{
    public bool $filterable = false;

    public function headers(): array
    {
        return ['id' => 'ID', 'title' => 'Title', 'published' => 'Published'];
    }

    public function renderCasts(): array
    {
        return ['published' => BoolCast::class];
    }

    public function renderColumnTitle(mixed $value, array $row): string
    {
        return '<u>' . e($value) . '</u>';
    }
}
