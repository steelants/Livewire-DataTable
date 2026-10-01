<?php

namespace Tests\Fixtures;

/**
 * renderRow() replaces casts and column methods for the whole row.
 */
class RenderRowComponent extends RenderComponent
{
    public function renderRow(array $row): array
    {
        return [
            'id'     => '<b>' . e($row['id']) . '</b>',
            'name'   => e($row['name']),
            'active' => $row['active'] ? 'on' : 'off',
            'note'   => e($row['note']),
            'raw'    => e($row['raw']),
        ];
    }
}
