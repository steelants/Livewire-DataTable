<?php

namespace Tests\Fixtures;

/**
 * Array driver with row() and column transformations - sorting runs on the transformed values.
 */
class ArrayTableWithRowComponent extends ArrayTableComponent
{
    public function row(array $row): array
    {
        return [
            'id'    => $row['id'],
            'name'  => $row['name'],
            'score' => $row['score'],
            'meta'  => ['rank' => 100 - (int) $row['score']],
        ];
    }

    public function columnName(mixed $value): string
    {
        return mb_strtoupper((string) $value);
    }
}
