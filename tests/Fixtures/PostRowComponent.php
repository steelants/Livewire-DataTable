<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

/**
 * Database driver with row() and a column transformation.
 */
class PostRowComponent extends PostTableComponent
{
    public function row(Model $post): array
    {
        return [
            'id'    => $post->id,
            'title' => 'row: ' . $post->title,
            'score' => $post->score * 10,
        ];
    }

    // Receives the model attribute, not the value returned from row().
    public function columnTitle(mixed $value): string
    {
        return mb_strtoupper((string) $value);
    }
}
